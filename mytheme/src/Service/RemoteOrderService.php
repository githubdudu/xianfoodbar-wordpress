<?php

namespace App\Service;

use App\Model\Order;
use App\Model\Desk;
use DateTime;



class RemoteOrderService
{
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

}
