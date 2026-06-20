<?php

namespace App\Service;

use App\Model\Order;
use App\Model\Desk;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class RemoteOrderService
{
  public static int $_12HOURS = 60 * 60 * 12;
  public static int $COMPLETED_STATUS = 2;
  private string $order_sn;
  private ?Desk $desk;
  private LoggerInterface $logger;

  public function __construct(?LoggerInterface $logger = null)
  {
    $this->logger = $logger ?? new NullLogger();
  }

  public function orderSync(array $orderData, int $desk_id): string
  {
    // Check if the order is created within 12 hours
    $date_created = $orderData['order']['date_created'];
    if (!$this->isWithin12hours($date_created)) {
      return 'skipped-stale';
    }

    // Check if the order already exists and is completed
    // getTakewayOrderById uses field 'takeway_order' to search for pattern 'orderdata_{order_id}'
    $existedOrder = Order::getTakewayOrderById($orderData['order_id']);
    if ($existedOrder && $existedOrder->order_status == self::$COMPLETED_STATUS) {
      return 'completed-existed';
    }

    $order = $existedOrder ?? new Order();
    $is_new = $existedOrder ? false : true;
    // Update the existed order according to the status
    // If the coming order is in trash, mark the existed order as deleted
    if (in_array($orderData['status'], ['trash'])) {
      if (!$is_new) {
        $order->is_delete = 1;
        $order->update();
      }
      return 'completed-trash';
    }
    // If the coming order is in failed or cancelled, mark the existed order as cancelled
    if (in_array($orderData['status'], ['failed', 'cancelled'])) {
      if (!$is_new) {
        $order->is_cancel = 1;
        $order->update();
      }
      return 'completed-failed-cancelled';
    }

    // If the order total cost is 0, skip the order
    if ($orderData['total'] == 0) {
      return 'skipped-no-total-cost';
    }

    // Return for existing order
    if (!$is_new) {
      $this->order_sn = $order->order_sn;
      return '更新完成';
    }

    // There might be a wrong code which should be if(!$desk) instead of if($desk). So right not the if block is never executed. 
    $this->desk = Desk::find($desk_id);
    if (!$this->desk) {
      $this->desk = Desk::where('is_takeway', 1)->first();
      $desk_id = $this->desk->id;
    }

    // Build the order by orderBuilder function
    $this->orderBuilder($orderData, $order, $is_new, $desk_id);
    
    // Expose the order_sn for external use
    $this->order_sn = $order->order_sn;

    // Save the order to database
    if (!$order->save()) {
      return '添加失败';
    }

    // build menu list and save order details (products in the order)
    $orderDetailService = new RemoteOrderDetail($order->oid, $orderData['items'], $this->logger);
    $orderDetailService->saveOrderDetails();

    return '创建完成';
  }

  /**
   * Write the contents of request to a file with the current date and time
   *
   * @param array $orderData The contents of the request
   *
   * @return   false | int
   *
   */
  public function saveOrderDataToFile(array $orderData): false|int
  {
    $result = file_put_contents(dirname(__DIR__, 2) . '/var/orderdata_' . date('YmdHis') . '.json', json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $result;
  }

  /**
   * Set up phone, realname, address, note
   *
   * @param array $orderData
   * @param Order $order
   * @param bool $is_new
   * @param int $desk_id
   *
   * @return void
   */
  public function orderBuilder(array $orderData, Order $order, bool $is_new, int $desk_id = 0): void
  {
    if (!$is_new) {
      // do something for existed order if needed
      return;
    }
    $order->phone = $orderData['phone'];
    $order->realname = $orderData['name'];
    $order->address = $orderData['address'];
    $order->note = $orderData['order']['customer_note'];

    $order->pay_price = $orderData['total'];
    $order->takeway_order = 'orderdata_' . $orderData['order_id'];
    $order->order_status = 1;
    // is_checked=0 order needs to be verified by human; is_checked=1 order will get into the kitchen system 
    $order->is_checked = 0;
    $order->is_takeway = 1;
    $order->desk_id = $desk_id;
    $order->pay_time = new \DateTime();
    $order->generateOrderSN();

    /**
     * Fields in metas
     *
     *  'is_vat_exempt' => '免增值税',
     * '_order_date' => '提取（希望送达）订单日期',
     * '_order_time' => '提取（送达）订单时间',
     * '_before_checkout_billing_form_pick_up_or_delivery' => '自取还是送餐',
     */
    if (isset($orderData['metas']['is_vat_exempt'])) {
      $order->is_vat_exempt = $orderData['metas']['is_vat_exempt'] == 'yes' ? 1 : 0;
    }

    if (isset($orderData['metas']['_before_checkout_billing_form_pick_up_or_delivery'])) {
      $order->is_delivery = $orderData['metas']['_before_checkout_billing_form_pick_up_or_delivery'] == 'delivery' ? 1 : 0;
    }

    $order->delivery_order_date = $this->getOrderExpectDateTime($orderData['metas'], $order->is_delivery);

    $order->pin_num = $this->getPinNum($order->desk_id);
    $order->is_pin = $order->pin_num > 0 ? 1 : 0;
  }


  /**
   * Get the expected datetime of the order.
   * There are two cases. One is expected pick up time, another is expected delivery time.
   *
   * @param array $metas The metadata of the order
   * @param int $is_delivery Whether the order is delivery or pickup, 0 is pickup, 1 is delivery
   * @return string The date and the time slot of the order
   */
  public function getOrderExpectDateTime(array $metas, ?int $is_delivery): string
  {
    if (!isset($metas['_order_date']) || $is_delivery === null) {
      return '';
    }

    $time = $is_delivery
      ? OrderTimeSlotParser::formatOrderExpectedDeliveryTime($metas['_order_estimated_delivery_time'])
      : OrderTimeSlotParser::formatOrderExpectedPickUpTime($metas['_order_time']);
    $date = OrderTimeSlotParser::formatOrderExpectedDate($metas['_order_date']);

    return $date . '     ' . $time;
  }

  /**
   *
   */
  public function getPinNum(int $desk_id, ?Order $order = null): int
  {
    $order = $order ?? new Order();
    $deskOrderCount = $order->getDeskOrderCountByDeskId($desk_id);

    if ($deskOrderCount == 0) return 0;

    $maxPinNum = $order->getDeskOrderMaxPinNumByDeskId($desk_id);

    return $maxPinNum ? $maxPinNum + 1 : 1;

  }

  public function getDesk(): Desk
  {
    return $this->desk;
  }

  public function getOrderSn(): string
  {
    return $this->order_sn;
  }

  /**
   * Check if the order is created within 12 hours
   *
   * @param array{date: string, timezone: string, timezone_type: int} $date_created
   *
   * @return bool
   */
  public function isWithin12hours(array $date_created): bool
  {
    $tz = new \DateTimeZone($date_created['timezone'] ?? 'Pacific/Auckland');
    $dt = \DateTime::createFromFormat('Y-m-d H:i:s.u', $date_created['date'], $tz);

    if (!$dt) {
      // fallback
      $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $date_created['date'], $tz);
    }
    $time = $dt->getTimestamp(); // UTC-normalised Unix timestamp

    $currentTime = time() - self::$_12HOURS;
    return $time >= $currentTime;
  }
}
