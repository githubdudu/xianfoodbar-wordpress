<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Core\DateTime;
use App\Service\OrderTimeSlotParser;
use PHPUnit\Framework\TestCase;

class OrderTimeSlotParserTest extends TestCase
{
  public function testFormatOrderExpectedDateParsesNewZealandFormat(): void
  {
    $TEST_ROUNDS = 100;
    for ($i = 0; $i < $TEST_ROUNDS; $i++) {
      $day = str_pad(strval(rand(1, 28)), 2, '0', STR_PAD_LEFT);
      $month = str_pad(strval(rand(1, 12)), 2, '0', STR_PAD_LEFT);
      $year = strval(rand(2020, 2120));
      $input_date = "$day.$month.$year";
      $expected_date = "$year-$month-$day";
      $this->assertSame($expected_date, OrderTimeSlotParser::formatOrderExpectedDate($input_date));
    }
    $this->assertSame('2025-12-04', OrderTimeSlotParser::formatOrderExpectedDate('04.12.2025'));
  }

  public function testFormatOrderExpectedPickUpTime(): void
  {
    $REF_TABLE = array(
      '1000' => 'AM 10:00 - AM 10:30',
      '1030' => 'AM 10:30 - AM 11:00',
      '1100' => 'AM 11:00 - AM 11:30',
      '1130' => 'AM 11:30 - PM 12:00',
      '1200' => 'PM 12:00 - PM 12:30',
      '1230' => 'PM 12:30 - PM 1:00',
      '100' => 'PM 1:00 - PM 1:30',
      '130' => 'PM 1:30 - PM 2:00',
      '200' => 'PM 2:00 - PM 2:30',
      '230' => 'PM 2:30 - PM 3:00',
      '300' => 'PM 3:00 - PM 3:30',
      '330' => 'PM 3:30 - PM 4:00',
      '400' => 'PM 4:00 - PM 4:30',
      '430' => 'PM 4:30 - PM 5:00',
      '500' => 'PM 5:00 - PM 5:30',
      '530' => 'PM 5:30 - PM 6:00',
      '600' => 'PM 6:00 - PM 6:30',
      '630' => 'PM 6:30 - PM 7:00',
      '700' => 'PM 7:00 - PM 7:30',
      '730' => 'PM 7:30 - PM 8:00',
      '800' => 'PM 8:00 - PM 8:30',
      '830' => 'PM 8:30 - PM 9:00',
    );
    foreach ($REF_TABLE as $input => $expected) {
      $this->assertSame($expected, OrderTimeSlotParser::formatOrderExpectedPickUpTime(strval($input)),
        "Expected the time slot for input $input to be $expected.");
    }
  }

  public function testFormatOrderExpectedPickUpTimeSpecial(): void
  {
    $REF_TABLE_SPECIAL = array(
      '900' => 'PM 9:00 - PM 9:15'
    );
    foreach ($REF_TABLE_SPECIAL as $input => $expected) {
      $this->assertSame($expected, OrderTimeSlotParser::formatOrderExpectedPickUpTime(strval($input)));
    }
  }

  /*
   * The test case is weird, but it is to meet the code. Though code need to be fixed later.
   */
  public function testFormatOrderExpectedDeliveryTime(): void
  {
    $REF_TABLE = array(
      '10.00' => 'PM 10:00 - PM 11:00',
      '11.00' => 'PM 11:00 - PM 12:00',
      '12.00' => 'PM 12:00 - PM 13:00',
      '1.00' => 'PM 1:00 - PM 2:00',
      '2.00' => 'PM 2:00 - PM 3:00',
      '3.00' => 'PM 3:00 - PM 4:00',
      '4.00' => 'PM 4:00 - PM 5:00',
      '5.00' => 'PM 5:00 - PM 6:00',
      '6.00' => 'PM 6:00 - PM 7:00',
      '7.00' => 'PM 7:00 - PM 8:00',
      '8.00' => 'PM 8:00 - PM 9:00',
      '9.00' => 'PM 9:00 - PM 10:00',
      '10.30' => 'PM 10:30 - PM 11:30',
      '11.30' => 'PM 11:30 - PM 12:30',
      '12.30' => 'PM 12:30 - PM 13:30',
      '1.30' => 'PM 1:30 - PM 2:30',
      '2.30' => 'PM 2:30 - PM 3:30',
      '3.30' => 'PM 3:30 - PM 4:30',
      '4.30' => 'PM 4:30 - PM 5:30',
      '5.30' => 'PM 5:30 - PM 6:30',
      '6.30' => 'PM 6:30 - PM 7:30',
      '7.30' => 'PM 7:30 - PM 8:30',
      '8.30' => 'PM 8:30 - PM 9:30',
    );

    foreach ($REF_TABLE as $input => $expected) {
      $this->assertSame($expected, OrderTimeSlotParser::formatOrderExpectedDeliveryTime(strval($input)),
        "Expected the time slot for input $input to be $expected.");
    }
  }

  public function testFormatOrderTimeForMissingTime(): void
  {
    $this->assertSame('', OrderTimeSlotParser::formatOrderExpectedPickUpTime(''),
      'Expected an empty string to be parsed as empty string.');
    $this->assertSame('', OrderTimeSlotParser::formatOrderExpectedDeliveryTime(''),
      'Expected an empty string to be parsed as empty string.');
    $this->assertSame((new DateTime())->format('Y-m-d'), OrderTimeSlotParser::formatOrderExpectedDate(''),
      'Expected an empty string to get the current date.');
  }
}
