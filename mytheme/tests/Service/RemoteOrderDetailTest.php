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

    $this->assertEquals(38, $actual);


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
    $actual = $this->remoteOrderDetail->checkUpgradeOption($item['meta_data'], $item['product_id']);

    $this->assertEquals(370, $actual);
  }

  public function testGetMenuIdFromItemName(): void
  {
    $names = [
      "2.加 大Extra Large (+&#36;2.00)" => 2,
      "3.水盆羊肉(配饼) (+&#36;2.50)" => 3,
      "135. 羊杂汤(配饼) (+&#36;2.50)" => 135,

      "39.清汤羊肉机切面" => 39,
      "22.加大Extra Large (+&#36;1.50)" => 22, // 21.清汤羊肉手扯面
      "40.臊子机切汤面." => 40,
      "24.加大Extra Large (+&#36;1.50)" => 24, // 23.臊子手扯汤面
      "32.加大Extra Large (+&#36;1.50)" => 32, // 31.羊汤手扯烩面
      "42.榨菜肉丝机切汤面" => 42,
      "30.加大Extra Large (+&#36;1.50)" => 30, // 29.榨菜肉丝手扯汤面
      "571.加大Extra Large (+&#36;1.50)" => 571, // 57.煎蛋肉丁手扯面
      "58.煎蛋肉丁机切面." => 58,
      "581.煎蛋肉丁机切面.(加大-Extra Large) (+&#36;1.50)" => 581,
      "591.加大Extra Large (+&#36;1.50)" => 591, // 59.羊汤荞面饸饹
      "51.麻辣牛肉机切面" => 51, // 301.麻辣牛肉手扯面
      "302.加大Extra Large (+&#36;1.50)" => 302, // 301.麻辣牛肉手扯面
      "54.红烧牛肉机切面" => 54, // 52.红烧牛肉手扯面
      "53.加大Extra Large (+&#36;1.50)" => 53, // 52.红烧牛肉手扯面
      "56.加大Extra Large (+&#36;1.50)" => 56, // 55.麻辣牛肉米线

      "26.加大Extra Large (+&#36;1.50)" => 26, //25.油泼手扯面
      "28.加大Extra Large (+&#36;1.50)" => 28, //27.炸酱手扯面
      "34.加大Extra Large (+&#36;1.50)" => 34, // 33.西红柿鸡蛋手扯面
      "36.加大Extra Large (+&#36;1.50)" => 36, // 35.臊子干拌手扯面
      "38.加大Extra Large (+&#36;1.50)" => 38, // 37.腊汁肉干拌手扯面
      "88.加大Extra Large (+&#36;1.50)" => 88, // 87.孜然羊肉干拌手扯面
      "99.加大Extra Large (+&#36;1.50)" => 99, // 98.孜然羊肉拉条
      "44.加大Extra Large (+&#36;1.50)" => 44, // 43.羊肉拉条
      "46.加大Extra Large (+&#36;1.50)" => 46, // 45.牛肉拉条
      "48.加大Extra Large (+&#36;1.50)" => 48, // 47.素拉条
      "50.加大Extra Large (+&#36;1.50)" => 50, // 49.油泼拉条
      "41.炸酱机切面" => 41, // 27.炸酱手扯面

      "16.加大Extra Large (+&#36;1.50)" => 16, // 15.拌面筋
      "62.加大Extra Large (+&#36;1.50)" => 62, // 61.酱牛肉
      "64.加大Extra Large (+&#36;1.50)" => 64, // 63.香麻牛肉
      "66.加大Extra Large (+&#36;1.50)" => 66, // 65.红油耳丝
      "68.加大Extra Large (+&#36;1.50)" => 68, // 67.夫妻肺片
      "82.加大Extra Large (+&#36;1.50)" => 82, // 81.干拌牛肉
      "72.加大Extra Large (+&#36;1.50)" => 72, // 71.红油肚丝
      "74.加大Extra Large (+&#36;1.50)" => 74, // 73.凉拌黄瓜
      "78.加大Extra Large (+&#36;1.50)" => 78, // 77.西芹豆干
      "80.加大Extra Large (+&#36;1.50)" => 80, // 79.凉拌海带丝

      "加菜，蛋，肉，面 Extras (+&#36;2.50)" => 0,
      "加菜，蛋，肉，面 Extras (+&#36;3.00)" => 0,
      "加菜，蛋，肉，面 Extras (+&#36;5.50)" => 0,
      "加菜，蛋，肉，面 Extras (+&#36;2.00)" => 0,
      "加菜，蛋，肉，面 Extras (+&#36;2.50) 加拉条" => 0,
      "_exoptions" => 0,
    ];


    foreach($names as $name => $expected) {

      $actual = $this->remoteOrderDetail->getMenuIdFromItemName($name);
      $this->assertEquals($expected, $actual);

    }
  }
}
