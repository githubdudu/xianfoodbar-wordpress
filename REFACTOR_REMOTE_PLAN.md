# Plan: Refactor Remote.php — DTO + Builder + Service

## Context

`Remote::getData()` is a 265-line method that does everything: parses a raw WooCommerce webhook array, constructs an Order with ~12 conditional attribute assignments, formats a delivery time-slot string, runs a 3-query pin-number calculation, persists OrderDetails in a loop, and sends notifications. This violates SRP and DIP, and duplicates the pin-number logic that already appears verbatim in 3 other controllers.

The goal is to separate HTTP concerns (stay in controller) from domain concerns (move to dedicated classes), using the **DTO** and **Builder** patterns, consistent with how `AdminMessage` / `AdminLogger` services already work in this codebase.

---

## New Files to Create

### 1. `src/DTO/RemoteOrderPayload.php`
Immutable value object. Replaces the 9 scattered local variables extracted from `$orderData`.

```php
namespace App\DTO;

final class RemoteOrderPayload
{
    public function __construct(
        public readonly string $phone,
        public readonly string $realname,
        public readonly string $address,
        public readonly string $note,
        public readonly string $allPrice,
        public readonly array  $menuOrder,
        public readonly string $createdDate,
        public readonly array  $metas,
        public readonly string $status,
    ) {}

    public static function fromWebhookArray(array $data): self
    {
        return new self(
            phone:       $data['phone'] ?? '',
            realname:    $data['name'] ?? '',
            address:     $data['address'] ?? '',
            note:        $data['order']['customer_note'] ?? '',
            allPrice:    $data['total'] ?? '0',
            menuOrder:   $data['items'] ?? [],
            createdDate: $data['order']['date_created']['date'] ?? '',
            metas:       $data['metas'] ?? [],
            status:      $data['status'] ?? '',
        );
    }
}
```

`$orderkey` (`order_key`) is intentionally omitted — it was dead code in the original.

---

### 2. `src/Builder/TakeawayOrderBuilder.php`
Encapsulates all "set the new order and its attributes" logic. No HTTP or WordPress dependencies — receives already-resolved values as arguments.

**Public interface:**
```php
public function build(
    RemoteOrderPayload $payload,
    Desk $desk,
    int $webhookId,
    ?Order $existing = null,
): Order
```

**Private methods** (all contained in the builder):
- `applyMetaFields(Order, array $metas)` — sets `is_vat_exempt`, `is_delivery`
- `applyDeliveryDateTime(Order, array $metas)` — formats the date string, delegates to:
  - `resolveTimeSlot(Order, array $metas): string` — picks pickup vs delivery path
  - `formatPickupSlot(string $rawTime): string` — the `mb_strcut` / AM-PM logic

**Public method** (intentionally public for reuse by other controllers):
```php
public function assignPinNumber(Order $order, Desk $desk): void
```
This replaces the identical 3-query block currently duplicated in:
- `Remote::getData()` (lines 178–206)
- `OrderController::addOrder()` (lines 59–86)
- `OrderAddController::sendOrders()` (lines 82–102)
- `OrderInfoController::editAdminOrder()` (lines 429–456 — has a bug: checks `pin_num => $deskOrderCount` instead of `is_pin => 1`)

---

### 3. `src/Service/TakeawayOrderDetailCreator.php`
Extracts the OrderDetail creation loop + Menu lookup from the controller.

**Public interface:**
```php
public function createFromPayload(int $oid, RemoteOrderPayload $payload): void
```

**Private method:**
```php
private function buildItemNote(array $item): string
```
Decodes WooCommerce HTML entity meta values into a plain note string.

The two-pass `$menuList` accumulation in the original collapses into a single pass (safe — `$menuList` was never read back after being appended).

---

## Modified File

### `src/Controller/Remote.php`

**What stays in the controller** (HTTP / infrastructure concerns):
- Route annotations
- `$this->request->request->all()` access
- `$this->getOption()` for desk resolution (WordPress option)
- `file_put_contents(...)` debug dump
- Staleness guard (`strtotime < time() - 43200`)
- Idempotency check (`Order::where('takeway_order', ...)`)
- `switch ($payload->status)` dispatch for trash/failed/cancelled
- `$order->save()` commit decision
- `$message->addMessage()` notification
- `$desk->use_status = 1; $desk->update()`
- `sendJson()` responses

**Injection:** `TakeawayOrderBuilder` and `TakeawayOrderDetailCreator` are added as **action-method arguments** on `getData()`, consistent with how `AdminMessage $message` is already injected. No constructor changes needed — `App\Builder\` and `App\Service\` are under `App\` which has `autowire: true` in `services.yaml`.

**Dead code removed from the method:**
- `$is_update = false` (assigned, never read)
- `$labels = [...]` array (defined, never used)
- `$endtime = strtotime(...)` + the commented-out block that used it
- `$orderkey = $orderData['order']['order_key']` (assigned, never used)

**After shape of `getData()`:**
```php
public function getData(
    AdminMessage $message,
    TakeawayOrderBuilder $builder,
    TakeawayOrderDetailCreator $detailCreator,
    ?int $id = null,
) {
    if (!$id) return $this->sendJson('', 404);

    $orderData = $this->request->request->all();
    if (empty($orderData)) return $this->sendJson('', 404);

    file_put_contents(...debug dump...);

    $payload  = RemoteOrderPayload::fromWebhookArray($orderData);
    $existing = Order::where('takeway_order', 'orderdata_' . $id)->first();
    $isNew    = ($existing === null);

    if (strtotime($payload->createdDate) < time() - 43200) {
        return $this->sendJson('', 200);
    }
    if ($existing?->order_status == 2) {
        return $this->sendJson('completed', 200);
    }

    switch ($payload->status) {
        case 'trash':    /* soft-delete $existing */ return ...;
        case 'failed':
        case 'cancelled': /* cancel $existing */     return ...;
    }

    $desk  = Desk::find((int) $this->getOption('site_takeway_did', 0))
               ? Desk::where('is_takeway', 1)->first()
               : null;

    $order = $builder->build($payload, $desk, $id, $existing);

    if ($order->pay_price == 0) return $this->sendJson('', 200);
    if (!$isNew)                return $this->sendJson('更新完成', 200);
    if (!$order->save())        return $this->sendJson('添加失败', 500);

    $detailCreator->createFromPayload($order->oid, $payload);
    $message->addMessage('订单通知', '有网站的新订单', musicFile: $this->getOption('takeway_type1_audio'));
    if ($desk) { $desk->use_status = 1; $desk->update(); }

    $this->addJsonData('data', ['order_id' => $order->order_sn]);
    return $this->sendJson('创建完成', 200);
}
```

---

## Responsibility Map (Before → After)

| Concern | Before | After |
|---|---|---|
| Webhook array parsing | 9 inline vars | `RemoteOrderPayload::fromWebhookArray()` |
| Basic field assignment | Inline in `getData()` | `TakeawayOrderBuilder::build()` |
| VAT / delivery-type meta | Inline in `getData()` | `TakeawayOrderBuilder::applyMetaFields()` |
| Time-slot formatting | Inline in `getData()` | `TakeawayOrderBuilder::formatPickupSlot()` |
| Pin number assignment | 4× duplicated across controllers | `TakeawayOrderBuilder::assignPinNumber()` |
| OrderDetail creation | Inline loop | `TakeawayOrderDetailCreator::createFromPayload()` |
| Status dispatch | Inline switch | Stays in controller (HTTP routing concern) |
| Desk via `getOption()` | Inline | Stays in controller (infrastructure) |
| Debug file dump | Inline | Stays in controller (I/O side-effect) |
| Notification | Inline | Stays in controller (side-effect after persist) |

---

## Implementation Order

1. Create `mytheme/src/DTO/RemoteOrderPayload.php` — no dependencies
2. Create `mytheme/src/Builder/TakeawayOrderBuilder.php` — depends on DTO + Order + Desk models
3. Create `mytheme/src/Service/TakeawayOrderDetailCreator.php` — depends on DTO + OrderDetail + Menu models
4. Rewrite `mytheme/src/Controller/Remote.php` — inject and delegate to the above

No changes to `config/services.yaml` required (autowire handles it).

---

## Verification

After implementing, test with the curl commands in README.md:

```bash
# Basic order
curl -X POST http://localhost:8080/api/remote/getdata/123 \
  -H "Content-Type: application/json" \
  -d '{ ... basic order payload ... }'

# Delivery order with extras and pickup time
curl -s -X POST http://localhost:8080/api/remote/getdata/789 \
  -H "Content-Type: application/json" \
  -d '{ ... delivery order payload with metas ... }'
```

Check:
- `var/orderdata_*.json` debug files are still written
- Admin panel order list shows the new order with correct fields
- `pin_num` increments correctly when a second order arrives at the same desk
- Delivery orders show correct `delivery_order_date` format
