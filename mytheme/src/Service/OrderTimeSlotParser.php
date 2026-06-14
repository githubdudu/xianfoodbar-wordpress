<?php

namespace App\Service;

use DateTime;

class OrderTimeSlotParser
{

  /**
   * Format the expected pickup time of the order to a time slot.
   * The time slot will be half an hour, for example, 1:00-1:30, 1:30-2:00. Start from the $time
   * If the order is delivery, the time slot will be one hour, for example, 1:00-2:00. Start from the $time
   * The original format is New Zealand time format which is hhmm
   *
   * @param string $time the time, for example 1030. If the time is 13:30, the time will be 130.
   *
   * @return string Formatted time slot
   */
  public static function formatOrderExpectedPickUpTime(string $time): string
  {
    $time_slot = '';
    if (isset($time) && !empty($time)) {
      // 1000, 1130, 1230
      if ($time >= 1000) {
        $hours = mb_strcut($time, 0, 2);
        $minutes = mb_strcut($time, 2, 2);

        if ($minutes == '30') {
          if ($hours >= '12') {
            // 1230 -> 100
            $next_hours = 1;
          } else {
            $next_hours = (intval($hours) + 1);
          }
        } else {
          $next_hours = $hours;
        }

        $time_slot = ($hours == 12 ? 'PM ' : 'AM ') . $hours . ':' . $minutes . ' - ' . ($next_hours == 1 || $next_hours == 12 ? 'PM ' : 'AM ') . $next_hours . ':' . ($minutes == '00' ? '30' : '00');
      } else {
        // 100, 130, 200, 230, 300, 330, 400, 430, 500, 530, 600, 630, 700, 730, 800, 830, 900, 930,
        $hours = mb_strcut($time, 0, 1);
        $minutes = mb_strcut($time, 1, 2);

        if ($minutes == '30') {
          $next_hours = (intval($hours) + 1);
        } else {
          $next_hours = $hours;
        }

        $time_slot = 'PM ' . $hours . ':' . $minutes . ' - PM ' . $next_hours . ':' . ($minutes == '00' ? ($next_hours == 9 ? '15' : '30') : '00');
      }
    }
    return $time_slot;
  }

  /**
   * Format the expected delivery time of the order to a time slot.
   * The time slot will be an hour, for example, 1:00-2:00, 2:00-3:00. Start from the $time
   * If the order is delivery, the time slot will be one hour, for example, 1:00-2:00. Start from the $time
   * The original format is New Zealand time format which is hhmm
   *
   * @param string $time the time, for example 10.30.
   * @return string Formatted time slot
   */
  public static function formatOrderExpectedDeliveryTime(string $time): string
  {
    $time_slot = '';
    if (isset($time) && !empty($time)) {
      list($hours, $minutes) = explode('.', $time);
      $time_slot = 'PM ' . $hours . ':' . $minutes . ' - ' . 'PM ' . (intval($hours) + 1) . ':' . $minutes;
    }
    return $time_slot;
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
  public static function formatOrderExpectedDate(string $origin_date): string
  {
    $date = new DateTime();
    $year = date('Y');
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
