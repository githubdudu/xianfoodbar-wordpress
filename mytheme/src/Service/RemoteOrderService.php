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
