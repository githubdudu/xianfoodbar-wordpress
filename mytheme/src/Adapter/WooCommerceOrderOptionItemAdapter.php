<?php

namespace App\Adapter;

class WooCommerceOrderOptionItemAdapter extends WooCommerceOrderItemAdapter implements OrderItemInterface
{
  /**
   * @param array{
   *   name: string,
   *   value: string,
   *   type_of_price: string,
   *   price: int,
   *   _type: string
   * } $rawItems
   *
   * @example
   * {
   * "name": "38.加大Extra Large",
   * "value": "L",
   * "type_of_price": "",
   * "price": 1.5,
   * "_type": ""
   * },
   * {
   * "name": "加菜，蛋，肉，面 Extras",
   * "value": "130.加菜.../份Extra Vegs",
   * "type_of_price": "",
   * "price": 2.5,
   * "_type": ""
   * },
   * {
   * "name": "加菜，蛋，肉，面 Extras",
   * "value": "131.加蛋.../个Extra Egg",
   * "type_of_price": "",
   * "price": 3,
   * "_type": ""
   * },
   * {
   * "name": "加菜，蛋，肉，面 Extras",
   * "value": "132.加肉.../份Extra Meat",
   * "type_of_price": "",
   * "price": 5.5,
   * "_type": ""
   * },
   * {
   * "name": "加菜，蛋，肉，面 Extras",
   * "value": "133.加面...Extra Noodles",
   * "type_of_price": "",
   * "price": 2,
   * "_type": ""
   * },
   * {
   * "name": "加菜，蛋，肉，面 Extras",
   * "value": "134.加拉条...Extra Stretched Noodles",
   * "type_of_price": "",
   * "price": 2.5,
   * "_type": ""
   * }
   *
   */
  public function __construct(array $rawItems)
  {
    parent::__construct($rawItems);
  }

  /**
   * Override. The global extra option always count as one
   *
   * @return int
   */
  public function getQuantity(): int
  {
    return 1;
  }

  /**
   * Override. The global extra option price is located in "price"
   *
   * @return string
   */
  public function getUnitPrice(): string
  {
    return $this->rawItem['price'];
  }

  /**
   * Get a string like this "132.加肉.../份Extra Meat"
   * Override. The global extra option string is located in "value"
   *
   * @return string
   */
  public function getMenuNumString(): string
  {
    return $this->rawItem['value'];
  }
}