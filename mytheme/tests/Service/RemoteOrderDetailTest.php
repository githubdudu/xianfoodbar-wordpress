<?php

namespace App\Tests\Service;

use App\Service\RemoteOrderDetail;
use PHPUnit\Framework\TestCase;

class RemoteOrderDetailTest extends TestCase
{
  private RemoteOrderDetail $remoteOrderDetail;
  private string $JSON_ITEMS = <<<'JSON'
    [
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "meta_data": [
          {
            "id": 38,
            "key": "38.加大Extra Large (+&#36;1.50)",
            "value": "L"
          },
          {
            "id": 132,
            "key": "加菜，蛋，肉，面 Extras (+&#36;5.50)",
            "value": "132.加肉.../份Extra Meat"
          },
          {
            "id": 371,
            "key": "371.加大Extra Large (+&#36;1.50)",
            "value": "腊汁肉机切面"
          }
        ]
      },
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "meta_data": [
          {
            "id": 38,
            "key": "38.加大Extra Large (+&#36;1.50)",
            "value": "L"
          }
        ]
      }
    ]
    JSON;

  public function setUp(): void
  {
    $items = json_decode($this->JSON_ITEMS, true);
    $this->remoteOrderDetail = new RemoteOrderDetail(999, $items);
  }

  public function testCheckUpgradeOption()
  {
    $JSON_ITEM = <<<'JSON'
      {
        "product_id": 100001,
        "quantity": 1,
        "subtotal": "25.00",
        "name": "37.腊汁肉干拌面",
        "meta_data": [
          {
            "id": 38,
            "key": "38.加大Extra Large (+&#36;1.50)",
            "value": "L"
          },
          {
            "id": 132,
            "key": "加菜，蛋，肉，面 Extras (+&#36;5.50)",
            "value": "132.加肉.../份Extra Meat"
          },
          {
            "id": 370,
            "key": "370.加大Extra Large (+&#36;1.50)",
            "value": "腊汁肉机切面"
          }
        ]
      }
      JSON;
    $item = json_decode($JSON_ITEM, true);
    $actual = $this->remoteOrderDetail->checkUpgradeOption($item['meta_data'], 37);

    $this->assertEquals(380, $actual);


    $JSON_ITEM = <<<'JSON'
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "meta_data": [
        ]
      }
      JSON;
    $item = json_decode($JSON_ITEM, true);
    $actual = $this->remoteOrderDetail->checkUpgradeOption($item['meta_data'], 37);

    $this->assertEquals(37, $actual);


    $JSON_ITEM = <<<'JSON'
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "meta_data": [
          {
            "id": 38,
            "key": "38.加大Extra Large (+&#36;1.50)",
            "value": "L"
          }
        ]
      }
      JSON;
    $item = json_decode($JSON_ITEM, true);
    $actual = $this->remoteOrderDetail->checkUpgradeOption($item['meta_data'], 37);

    $this -> assertEquals(38, $actual);


    $JSON_ITEM = <<<'JSON'
      {
        "product_id": 37,
        "quantity": 1,
        "subtotal": "25.00",
        "meta_data": [
          {
            "id": 370,
            "key": "370.加大Extra Large (+&#36;1.50)",
            "value": "腊汁肉机切面"
          }
        ]
      }
      JSON;
    $item = json_decode($JSON_ITEM, true);
    $actual = $this->remoteOrderDetail->checkUpgradeOption($item['meta_data'],$item['product_id']);

    $this -> assertEquals(370, $actual);
  }
}
