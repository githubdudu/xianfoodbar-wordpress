<?php

namespace App\Tests\Adapter;

use App\Adapter\WooCommerceOrderItemAdapterFactory;
use App\Adapter\WooCommerceOrderItemListAdapterFactory;
use PHPUnit\Framework\TestCase;

class WooCommerceOrderItemListAdapterFactoryTest extends TestCase
{

  /**
   * The test is highly dependent on the data in the database.
   * Tested with a test database if needed.
   */
  public function testCreateList()
  {

    $items = [
      [
        "product_id" => 37,
        "quantity" => 13,
        "subtotal" => "25.00",
        "name" => "37.腊汁肉干拌面",
        "meta_data" => [
          [
            "id" => 38,
            "key" => "38.加大Extra Large (+&#36;1.50)",
            "value" => "L"
          ],
          [
            "id" => 132,
            "key" => "加菜，蛋，肉，面 Extras (+&#36;5.50)",
            "value" => "132.加肉.../份Extra Meat"
          ],
          [
            "id" => 370,
            "key" => "370.腊汁肉机切面",
            "value" => "腊汁肉机切面"
          ],
          [
            "id" => "332",
            "key" => "_exoptions",
            "value" => [
              [
                "name" => "38.加大Extra Large",
                "value" => "L",
                "type_of_price" => "",
                "price" => 1.5,
                "_type" => ""
              ],
              [
                "name" => "加菜，蛋，肉，面 Extras",
                "value" => "132.加肉.../份Extra Meat",
                "type_of_price" => "",
                "price" => 5.5,
                "_type" => ""
              ],
              [
                "name" => "370.腊汁肉机切面",
                "value" => "Plain noodles",
                "type_of_price" => "",
                "price" => 0,
                "_type" => ""
              ],
            ]
          ]
        ]
      ],
      [
        "product_id" => 37,
        "quantity" => 1,
        "subtotal" => "21.00",
        "name" => "37.腊汁肉干拌面",
        "meta_data" => [
          [
            "id" => 38,
            "key" => "38.加大Extra Large (+&#36;1.50)",
            "value" => "L"
          ],
          [
            "id" => "332",
            "key" => "_exoptions",
            "value" => [
              [
                "name" => "38.加大Extra Large",
                "value" => "L",
                "type_of_price" => "",
                "price" => 1.5,
                "_type" => ""
              ]
            ]
          ]
        ]
      ],
      [
        "product_id" => 37,
        "quantity" => 3,
        "subtotal" => "11.00",
        "name" => "37.腊汁肉干拌面",
        "meta_data" => [
        ]
      ]
    ];

    $preparedItems = (new WooCommerceOrderItemListAdapterFactory(
      new WooCommerceOrderItemAdapterFactory()
    ))->createList($items);

    self::assertCount(4, $preparedItems);

    self::assertSame(380, $preparedItems[0]->getMenuNum());
    self::assertSame("25.00", $preparedItems[0]->getUnitPrice());
    self::assertSame(13, $preparedItems[0]->getQuantity());
    self::assertSame("38.加大Extra Large (+&#36;1.50) -> L\n" .
      "加菜，蛋，肉，面 Extras (+&#36;5.50) -> 132.加肉.../份Extra Meat\n" .
      "370.腊汁肉机切面 -> 腊汁肉机切面\n"
      , $preparedItems[0]->getNote());

    self::assertSame(132, $preparedItems[1]->getMenuNum());
    self::assertSame("5.5", $preparedItems[1]->getUnitPrice());
    self::assertSame(1, $preparedItems[1]->getQuantity());
    self::assertSame("", $preparedItems[1]->getNote());

    self::assertSame(38, $preparedItems[2]->getMenuNum());
    self::assertSame("21.00", $preparedItems[2]->getUnitPrice());
    self::assertSame(1, $preparedItems[2]->getQuantity());
    self::assertSame("38.加大Extra Large (+&#36;1.50) -> L\n", $preparedItems[2]->getNote());

    self::assertSame(37, $preparedItems[3]->getMenuNum());
    self::assertSame("11.00", $preparedItems[3]->getUnitPrice());
    self::assertSame(3, $preparedItems[3]->getQuantity());
    self::assertSame("", $preparedItems[3]->getNote());
  }
}
