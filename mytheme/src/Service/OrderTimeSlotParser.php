<?php

namespace App\Service;

use DateTime;

class OrderTimeSlotParser
{

  /**
   * Format the time of the order to a time slot. 
   * If the order is pickup, the time slot will be half an hour, for example, 1:00-1:30, 1:30-2:00. Start from the $time
   * If the order is delivery, the time slot will be one hour, for example, 1:00-2:00. Start from the $time
   * The original format is New Zealand time format which is hhmm
   * 
   * @param string $time the time, for example 1030. If the time is 13:30, the time will be 130.
   * @param bool $is_delivery Whether the order is delivery or pickup, 0 is pickup, 1 is delivery
   * 
   * @return string Formatted time slot
   */
  public static function formatOrderDeliveryTime(string $time, bool $is_delivery): string
  {
    $time = '';
    if ($is_delivery == 0 && isset($metas['_order_time']) && !empty($metas['_order_time'])) {
      // 1000, 1130, 1230
      if ($metas['_order_time'] > 1000) {
        $time1 = mb_strcut($metas['_order_time'], 0, 2);
        $time2 = mb_strcut($metas['_order_time'], 2, 2);

        if ($time2 == '30') {
          if ($time1 >= '12') {
            // 1230 -> 100
            $next_time1 = 1;
          } else {
            $next_time1 = (intval($time1) + 1);
          }
        } else {
          $next_time1 = $time1;
        }

        $time = ($time1 == 12 ? 'PM ' : 'AM ') . $time1 . ':' . $time2 . ' - ' . ($next_time1 == 1 || $next_time1 == 12 ? 'PM ' : 'AM ') . $next_time1 . ':' . ($time2 == '00' ? '30' : '00');
      } else {
        // 100, 130, 200, 230, 300, 330, 400, 430, 500, 530, 600, 630, 700, 730, 800, 830, 900
        $time1 = mb_strcut($metas['_order_time'], 0, 1);
        $time2 = mb_strcut($metas['_order_time'], 1, 2);

        if ($time2 == '30') {
          $next_time1 = (intval($time1) + 1);
        } else {
          $next_time1 = $time1;
        }

        $time = 'PM ' . $time1 . ':' . $time2 . ' - PM ' . $next_time1 . ':' . ($time2 == '00' ? ($next_time1 == 9 ? '15' : '30') : '00');
      }
    } else if ($is_delivery == 1 && isset($metas['_order_estimated_delivery_time']) && !empty($metas['_order_estimated_delivery_time'])) {
      list($time1, $time2) = explode('.', $metas['_order_estimated_delivery_time']);
      $time = 'PM' . $time1 . ':' . $time2 . ' - ' . 'PM ' . (intval($time1) + 1) . ':' . $time2;
    }
    return $time;
  }

  /**
   * Format the date of the order. 
   * The original format is New Zealand date format which is dd.mm.yyyy
   * "04.12.2025" -> "2025-12-04"
   * 
   * @param string $origin_date
   * 
   * @return string Formatted date
   */
  public static function formatOrderDeliveryDate(string $origin_date): string
  {
    $date = new DateTime();
    $year = date('y');
    $day = date('d');
    $month = date('m');
    if (str_contains($origin_date, '.')) {
      // New Zealand date format which is dd.mm.yyyy
      list($day, $month, $year) = explode('.', $origin_date, 3);
    }

    $date->setDate($year, $month, $day);
    return $date->format('Y-m-d');
  }
}
