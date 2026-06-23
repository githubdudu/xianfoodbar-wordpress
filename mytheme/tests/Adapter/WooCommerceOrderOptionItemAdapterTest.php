<?php

namespace App\Tests\Adapter;

use App\Adapter\WooCommerceOrderOptionItemAdapter;
use PHPUnit\Framework\TestCase;

class WooCommerceOrderOptionItemAdapterTest extends TestCase
{
  private WooCommerceOrderOptionItemAdapter $WCOrderOptionItemAdapter;

  public function setUp(): void
  {
    $item = [
      "name" => "加菜，蛋，肉，面 Extras",
      "value" => "132.加肉.../份Extra Meat",
      "type_of_price" => "",
      "price" => 5.5,
      "_type" => ""
    ];

    $this->WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);
  }

  public function testInvalidOptionItem(): void
  {
    $item = [
      "name" => "38.加大Extra Large",
      "value" => "L",
      "type_of_price" => "",
      "price" => 1.5,
      "_type" => ""
    ];
    $WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);

    self::assertSame(0, $WCOrderOptionItemAdapter->getMenuNum());
    self::assertSame('1.5', $WCOrderOptionItemAdapter->getUnitPrice());
    self::assertSame(1, $WCOrderOptionItemAdapter->getQuantity());
    self::assertSame("", $WCOrderOptionItemAdapter->getNote());
  }

  public function testValidOptionItem130()
  {
    $item = [
      "name" => "加菜，蛋，肉，面 Extras",
      "value" => "130.加菜...\/份Extra Vegs",
      "type_of_price" => "",
      "price" => 2.5,
      "_type" => ""
    ];

    $WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);

    self::assertSame(130, $WCOrderOptionItemAdapter->getMenuNum());
    self::assertSame('2.5', $WCOrderOptionItemAdapter->getUnitPrice());
    self::assertSame(1, $WCOrderOptionItemAdapter->getQuantity());
    self::assertSame("", $WCOrderOptionItemAdapter->getNote());
  }

  public function testValidOptionItem131()
  {
    $item = [
      "name" => "加菜，蛋，肉，面 Extras",
      "value" => "131.加蛋...\/个Extra Egg",
      "type_of_price" => "",
      "price" => 3,
      "_type" => ""
    ];

    $WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);

    self::assertSame(131, $WCOrderOptionItemAdapter->getMenuNum());
    self::assertSame('3', $WCOrderOptionItemAdapter->getUnitPrice());
    self::assertSame(1, $WCOrderOptionItemAdapter->getQuantity());
    self::assertSame("", $WCOrderOptionItemAdapter->getNote());
  }

  public function testValidOptionItem132()
  {
    $item = [
      "name" => "加菜，蛋，肉，面 Extras",
      "value" => "132.加肉...\/份Extra Meat",
      "type_of_price" => "",
      "price" => 5.5,
      "_type" => ""
    ];

    $WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);

    self::assertSame(132, $WCOrderOptionItemAdapter->getMenuNum());
    self::assertSame('5.5', $WCOrderOptionItemAdapter->getUnitPrice());
    self::assertSame(1, $WCOrderOptionItemAdapter->getQuantity());
    self::assertSame("", $WCOrderOptionItemAdapter->getNote());
  }

  public function testValidOptionItem133()
  {
    $item = [
      "name" => "加菜，蛋，肉，面 Extras",
      "value" => "133.加面...Extra Noodles",
      "type_of_price" => "",
      "price" => 2,
      "_type" => ""
    ];

    $WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);

    self::assertSame(133, $WCOrderOptionItemAdapter->getMenuNum());
    self::assertSame('2', $WCOrderOptionItemAdapter->getUnitPrice());
    self::assertSame(1, $WCOrderOptionItemAdapter->getQuantity());
    self::assertSame("", $WCOrderOptionItemAdapter->getNote());
  }

  public function testValidOptionItem134()
  {
    $item = [
      "name" => "加菜，蛋，肉，面 Extras",
      "value" => "134.加拉条...Extra Stretched Noodles",
      "type_of_price" => "",
      "price" => 2.5,
      "_type" => ""
    ];

    $WCOrderOptionItemAdapter = new WooCommerceOrderOptionItemAdapter($item);

    self::assertSame(134, $WCOrderOptionItemAdapter->getMenuNum());
    self::assertSame('2.5', $WCOrderOptionItemAdapter->getUnitPrice());
    self::assertSame(1, $WCOrderOptionItemAdapter->getQuantity());
    self::assertSame("", $WCOrderOptionItemAdapter->getNote());
  }

  public function testGetMenuNumString()
  {
    $actual = $this->WCOrderOptionItemAdapter->getMenuNumString();
    $this->assertSame("132.加肉.../份Extra Meat", $actual);
  }
}
