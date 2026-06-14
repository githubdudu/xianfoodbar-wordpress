<?php

namespace App\Tests\Service;

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
        'date_created' => [
          'date' => '2024-06-01 12:00:00'
        ]
      ],
      'status' => 'completed',
      'total' => 100
    ];

    $this->existedOrder = new Order();
    $this->existedOrder->oid = 123;
    $this->existedOrder->order_status = 2;
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

  public function testOrderSync()
  {
    // Orders created more than 12 hours ago should return 'skipped'
    $this->orderData['order']['date_created']['date'] = date('Y-m-d H:i:s', time() - (RemoteOrderService::$_12HOURS + 1));
    $this->assertSame('skipped', (new RemoteOrderService())->orderSync($this->orderData, 1));

    // the order already exists and is completed should return 'completed'
    $this->orderData['order']['date_created']['date'] = date('Y-m-d H:i:s');
    $this->orderData['order_id'] = 123456;
    // mock to return an order with completed status
    $this->setupWpDb(nextResults: [(object)[
      'order_status' => RemoteOrderService::$COMPLETED_STATUS,
      'takeway_order' => 'orderdata_123456' // getById uses 'takeway_order' to search for pattern 'orderdata_{order_id}'
    ]]);
    $this->assertSame('completed', (new RemoteOrderService())->orderSync($this->orderData, 1));
  }

  public function testSaveOrderDataToFile()
  {

  }

  public function testOrderBuilder()
  {

  }

  public function testGetOrderSn()
  {

  }

  public function testGetDesk()
  {

  }

  public function testGetPinNum()
  {

  }

  public function testGetOrderExpectDateTime()
  {

  }
}
