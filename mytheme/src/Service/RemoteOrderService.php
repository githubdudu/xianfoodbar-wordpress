<?php

namespace App\Service;

use App\Model\Order;
use App\Model\Desk;
use DateTime;



class RemoteOrderService
{
  private static int $_12HOURS = 60 * 60 * 12;

  public function orderSync(array $orderData, int $desk_id): string
  {
    // Check if the order is created within 12 hours
    $date = $orderData['order']['date_created']['date'];
    if (!$this->isWithin12hours($date)) {
      return 'skipped';
    }

    // Check if the order already exists and is completed
    $existedOrder = Order::getTakewayOrderById($orderData['order_id']);
    if ($existedOrder && $existedOrder->order_status == self::$COMPLETED_STATUS) {
      return 'completed';
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
      return 'completed';
    }
    // If the coming order is in failed or cancelled, mark the existed order as cancelled
    if (in_array($orderData['status'], ['failed', 'cancelled'])) {
      if (!$is_new) {
        $order->is_cancel = 1;
        $order->update();
      }
      return 'completed';
    }

    // If the order total cost is 0, skip the order
    if ($orderData['total'] == 0) {
      return 'skipped';
    }

    // There might be a wrong code which should be if(!$desk) instead of if($desk). So right not the if block is never executed. 
    $desk = Desk::find($desk_id);
    if ($desk) {
      $desk = Desk::where('is_takeway', 1)->first();
      $desk_id = $desk->id;
    }

    $this->orderBuilder($orderData, $order, $is_new, $desk_id);

    return '';
  }

  /**
   * Write the contents of request to a file with the current date and time
   *
   * @param    array  $orderData  The contents of the request
   *
   * @return   null
   *
   */
  public function saveOrderDataToFile($orderData)
  {
    file_put_contents(dirname(__DIR__, 2) . '/var/orderdata_' . date('YmdHis') . '.json', json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
  public function orderBuilder(array $orderData, Order $order, bool $is_new, int $desk_id = 0)
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
  }


  /**
   * Check if the order is created within 12 hours
   * 
   * @param string $date
   * @return bool
   */
  private function isWithin12hours(string $date): bool
  {
    $time = strtotime($date);

    return $time >= time() - self::$_12HOURS;
  }
}
