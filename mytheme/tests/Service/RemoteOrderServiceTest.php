<?php

namespace App\Tests\Service;

use App\Core\DateTime;
use App\Model\Order;
use App\Service\RemoteOrderService;
use Dbout\WpOrm\Orm\Database;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use wpdb;

class RemoteOrderServiceTest extends TestCase
{
  private array $orderData;
  private Order $existedOrder;

  public function setUp(): void
  {
    $this->orderData = [
      'order_id' => 123,
      'order' => [
        'customer_note' => '测试订单',
        'order_key' => '20230512123456',
        'date_created' => [
          'date' => '2024-06-01 12:00:00.000000',
          'timezone_type' => 3,
          'timezone' => 'Pacific/Auckland',
        ]
      ],
      'status' => 'completed',
      'total' => 100,
      'phone' => '18888888888',
      'name' => '测试订单name',
      'address' => '测试订单address',
      'metas' => [
        '_order_date' => '04.12.2025',
        '_order_time' => '730',
        '_order_estimated_delivery_time' => '10.30',
        '_before_checkout_billing_form_pick_up_or_delivery' => 'delivery',
        'is_vat_exempt' => 'yes',
      ],
    ];

    $this->existedOrder = new Order();
    $this->existedOrder->oid = 123;
    $this->existedOrder->order_status = 1;
    $this->existedOrder->is_delete = 0;
    $this->existedOrder->is_cancel = 0;
    $this->existedOrder->takeway_order = 'orderdata_123';
    $this->existedOrder->generateOrderSN();
  }


  /**
   * Eloquent's hydration (Model::newFromBuilder()) does (array) $row to read columns,
   * which only works for a plain stdClass/array row — casting a live Order model instead
   * yields its mangled internal properties (not 'order_sn' etc.) so those columns come
   * back empty. Convert the fixture to a row shape before stubbing $wpdb with it.
   */
  private function existedOrderRow(): object
  {
    return (object) $this->existedOrder->getAttributes();
  }

  /**
   * Replace the $wpdb stub and reset the WP-ORM Database singleton so it
   * picks up the new stub on the next query.
   */
  private function setupWpDb(array $nextResults = [], mixed $nextRow = null): void
  {
    $mock = new wpdb();
    $mock->nextResults = $nextResults;
    $mock->nextRow = $nextRow;
    $GLOBALS['wpdb'] = $mock;

    $ref = new ReflectionClass(Database::class);
    $prop = $ref->getProperty('instance');
    // In newer PHP versions (8.1+), ReflectionProperty::setAccessible() is effectively unnecessary for
    // many reflection operations and has been deprecated in PHP 8.5.
    // Reflection can already access private/protected properties in most cases.
    $prop->setValue(null, null);
  }

  public function testOrderSync_skip()
  {
    // Orders created more than 12 hours ago should return 'skipped'
    $this->orderData['order']['date_created']['date'] = (new DateTime('-12hours -1second', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->assertSame('skipped-stale', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testOrderSync_Completed()
  {
    // the order already exists and is completed should return 'completed'
    $this->orderData['order']['date_created']['date'] = (new DateTime('-12 hours +1 second', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->orderData['order_id'] = 123456;
    // mock to return an order with completed status
    $this->setupWpDb(nextResults: [(object)[
      'order_status' => RemoteOrderService::$COMPLETED_STATUS,
      'takeway_order' => 'orderdata_123456' // getById uses 'takeway_order' to search for pattern 'orderdata_{order_id}'
    ]]);
    $this->assertNotSame('skipped-stale', (new RemoteOrderService())->orderSync($this->orderData, 1));
    $this->assertSame('completed-existed', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testOrderSync_trash()
  {
    // the order placed one hour ago but is marked as deleted should return 'trash'
    $this->orderData['order']['date_created']['date'] = (new DateTime('-1 hours', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->orderData['order_id'] = 123456;

    // If the coming order is in trash, mark the existed order as deleted
    $this->orderData['status'] = 'trash';
    $this->setupWpDb(nextResults: [$this->existedOrderRow()]);
    $this->assertSame('completed-trash', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testOrderSync_cancel()
  {
    // If the coming order is in failed or cancelled, mark the existed order as cancelled
    $this->orderData['order']['date_created']['date'] = (new DateTime('-1 hours', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->orderData['order_id'] = 123456;

    $this->orderData['status'] = 'failed';
    $this->setupWpDb(nextResults: [$this->existedOrderRow()]);

    $this->assertSame('completed-failed-cancelled', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testOrderSync_failed()
  {
    // If the coming order is in failed or cancelled, mark the existed order as cancelled
    $this->orderData['order']['date_created']['date'] = (new DateTime('-1 hours', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->orderData['order_id'] = 123456;

    $this->orderData['status'] = 'cancelled';
    $this->setupWpDb(nextResults: [$this->existedOrderRow()]);

    $this->assertSame('completed-failed-cancelled', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testOrderSync_zeroTotal()
  {
    // Orders with zero total should return 'skipped'
    $this->orderData['order']['date_created']['date'] = (new DateTime('-1 hours', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->orderData['order_id'] = 123456;
    $this->orderData['status'] = 'completed';

    $this->setupWpDb(nextResults: [$this->existedOrderRow()]);

    $this->orderData['total'] = 0;
    $this->assertSame('skipped-no-total-cost', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testOrderSync_return()
  {
    // Return for existing order if all cases above are not hit
    $this->orderData['order']['date_created']['date'] = (new DateTime('-1 hours', new \DateTimeZone('Pacific/Auckland')))
      ->format('Y-m-d H:i:s.u');
    $this->orderData['order_id'] = 123456;
    $this->orderData['status'] = 'completed';
    $this->setupWpDb(nextResults: [$this->existedOrderRow()]);

    $this->assertSame('更新完成', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testSaveOrderDataToFile()
  {
    // Test function saveOrderDataToFile(array $orderData)
    $service = new RemoteOrderService();

    $before = time();
    $result = $service->saveOrderDataToFile($this->orderData);

    // Should always return null
    $this->assertNotFalse($result);

    // The file is written to <project_root>/var/orderdata_YmdHis.json
    // dirname(__DIR__, 2) from src/Service/ resolves to the mytheme root
    $varDir = dirname(__DIR__, 2) . '/var';
    $pattern = $varDir . '/orderdata_*.json';

    // Find files created at or after $before
    $files = array_filter(glob($pattern), fn($f) => filemtime($f) >= $before);
    $this->assertNotEmpty($files, 'Expected a new orderdata file to be created in var/');

    // Pick the most recently modified file
    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
    $createdFile = $files[0];

    // Verify content matches the encoded order data
    $expected = json_encode($this->orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $this->assertSame($expected, file_get_contents($createdFile));

    // Clean up
    unlink($createdFile);
  }

  public function testOrderBuilder()
  {
    // Case 1: $is_new = false — returns early, Order fields untouched
    $order = new Order();
    (new RemoteOrderService())->orderBuilder($this->orderData, $order, false, 1);
    $this->assertNull($order->phone);

    // Case 2: $is_new = true — all fields populated
    // getPinNum calls count() internally; mock wpdb aggregate=0 so it returns 0 immediately
    $this->setupWpDb(nextResults: [(object)['aggregate' => 0]]);
    $order = new Order();
    (new RemoteOrderService())->orderBuilder($this->orderData, $order, true, 1);

    $this->assertSame($this->orderData['phone'], $order->phone);
    $this->assertSame($this->orderData['name'], $order->realname);
    $this->assertSame($this->orderData['address'], $order->address);
    $this->assertSame($this->orderData['order']['customer_note'], $order->note);
    $this->assertSame($this->orderData['total'], $order->pay_price);
    $this->assertSame('orderdata_' . $this->orderData['order_id'], $order->takeway_order);
    $this->assertSame(1, $order->order_status);
    $this->assertSame(0, $order->is_checked);
    $this->assertSame(1, $order->is_takeway);
    $this->assertSame(1, $order->desk_id);
    $this->assertInstanceOf(\DateTime::class, $order->pay_time);
    $this->assertRegExp('/^od\d{18}$/', $order->order_sn);
    $this->assertSame(1, $order->is_vat_exempt);
    $this->assertSame(1, $order->is_delivery);
    $this->assertSame(0, $order->pin_num);
    $this->assertSame(0, $order->is_pin);

    // Case 3: is_vat_exempt absent — field not set
    $this->setupWpDb(nextResults: [(object)['aggregate' => 0]]);
    $data = $this->orderData;
    unset($data['metas']['is_vat_exempt']);
    $order = new Order();
    (new RemoteOrderService())->orderBuilder($data, $order, true, 1);
    $this->assertNull($order->is_vat_exempt);

    // Case 4: pickup (not delivery) — is_delivery = 0
    $this->setupWpDb(nextResults: [(object)['aggregate' => 0]]);
    $data = $this->orderData;
    $data['metas']['_before_checkout_billing_form_pick_up_or_delivery'] = 'pickup';
    $order = new Order();
    (new RemoteOrderService())->orderBuilder($data, $order, true, 1);
    $this->assertSame(0, $order->is_delivery);
  }

  public function testGetPinNum()
  {
    $order_stub = new class extends Order {
      public function getDeskOrderCountByDeskId(int $desk_id): int { return 1; }
      public function getDeskOrderMaxPinNumByDeskId(int $desk_id) { return null; }
    };

    $service = new RemoteOrderService();
    $result = $service->getPinNum(5, $order_stub);
    $this->assertIsInt(0);
  }

  public function testGetPinNumReturnsOne(): void
  {
    $order_stub = new class extends Order {
      public function getDeskOrderCountByDeskId(int $desk_id): int { return 1; }
      public function getDeskOrderMaxPinNumByDeskId(int $desk_id) { return null; }
    };

    $service = new RemoteOrderService();
    $result = $service->getPinNum(1, $order_stub);

    $this->assertSame(1, $result);
  }

  public function testGetPinNumReturnsNextPinNumber(): void
  {
    $order_stub = new class extends Order {
      public function getDeskOrderCountByDeskId(int $desk_id): int { return 1; }
      public function getDeskOrderMaxPinNumByDeskId(int $desk_id) { return 2; }
    };
    $service = new RemoteOrderService();
    $result = $service->getPinNum(1, $order_stub);

    $this->assertSame(3, $result, 'max pin number is 2, the next pin number should be 3');

    $order_stub = new class extends Order {
      public function getDeskOrderCountByDeskId(int $desk_id): int { return 1; }
      public function getDeskOrderMaxPinNumByDeskId(int $desk_id) { return 5; }
    };
    $service = new RemoteOrderService();
    $result = $service->getPinNum(1, $order_stub);

    $this->assertSame(6, $result, 'max pin number is 5, the next pin number should be 6');
  }

  public function testGetOrderExpectDateTime()
  {
    $meta = [
      '_order_date' => '04.12.2025',
      '_order_time' => 730,
      '_order_estimated_delivery_time' => '10.30'
    ];
    $is_delivery = true;
    $expected = '2025-12-04' . '     ' . 'PM 10:30 - PM 11:30';
    $actual = (new RemoteOrderService())->getOrderExpectDateTime($meta, $is_delivery);
    $this->assertSame($expected, $actual);

    $meta = [
      '_order_date' => '12.04.2025',
      '_order_time' => 730,
      '_order_estimated_delivery_time' => '10.30'
    ];
    $is_delivery = false;
    $expected = '2025-04-12' . '     ' . 'PM 7:30 - PM 8:00';
    $actual = (new RemoteOrderService())->getOrderExpectDateTime($meta, $is_delivery);
    $this->assertSame($expected, $actual);

    $meta = [
      '_order_date' => '04.12.2025',
      '_order_time' => 1030,
      '_order_estimated_delivery_time' => '10.30'
    ];
    $is_delivery = false;
    $expected = '2025-12-04' . '     ' . 'AM 10:30 - AM 11:00';
    $actual = (new RemoteOrderService())->getOrderExpectDateTime($meta, $is_delivery);
    $this->assertSame($expected, $actual);

    $meta = [
      '_order_date' => '04.12.2025',
      '_order_time' => 1130,
      '_order_estimated_delivery_time' => '10.30'
    ];
    $is_delivery = false;
    $expected = '2025-12-04' . '     ' . 'AM 11:30 - PM 12:00';
    $actual = (new RemoteOrderService())->getOrderExpectDateTime($meta, $is_delivery);
    $this->assertSame($expected, $actual);
  }

  public function testisWithin12hours()
  {
    $removeOrderService = new RemoteOrderService();

    $date_created = [
      'date' => (new DateTime('-12 hours -1 second', new \DateTimeZone('Pacific/Auckland')))->format('Y-m-d H:i:s.u'),
      'timezone_type' => 3,
      'timezone' => 'Pacific/Auckland',
    ];
    $result = $removeOrderService->isWithin12hours($date_created);
    $this->assertFalse($result);

    $date_created = [
      'date' => (new DateTime('-100 days', new \DateTimeZone('Pacific/Auckland')))->format('Y-m-d H:i:s.u'),
      'timezone_type' => 3,
      'timezone' => 'Pacific/Auckland',
    ];
    $result = $removeOrderService->isWithin12hours($date_created);
    $this->assertFalse($result);

    $date_created = [
      'date' => (new DateTime('-12 hours +1 second', new \DateTimeZone('Pacific/Auckland')))->format('Y-m-d H:i:s.u'),
      'timezone_type' => 3,
      'timezone' => 'Pacific/Auckland',
    ];
    $result = $removeOrderService->isWithin12hours($date_created);
    $this->assertTrue($result);

    $date_created = [
      'date' => (new DateTime('-1 second', new \DateTimeZone('Pacific/Auckland')))->format('Y-m-d H:i:s.u'),
      'timezone_type' => 3,
      'timezone' => 'Pacific/Auckland',
    ];
    $result = $removeOrderService->isWithin12hours($date_created);
    $this->assertTrue($result);

    $date_created = [
      'date' => (new DateTime('+1 hour', new \DateTimeZone('Pacific/Auckland')))->format('Y-m-d H:i:s.u'),
      'timezone_type' => 3,
      'timezone' => 'Pacific/Auckland',
    ];
    $result = $removeOrderService->isWithin12hours($date_created);
    $this->assertTrue($result);
  }
}
