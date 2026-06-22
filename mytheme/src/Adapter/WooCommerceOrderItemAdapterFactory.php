<?php

namespace App\Adapter;

class WooCommerceOrderItemAdapterFactory implements OrderItemFactoryInterface
{

  /**
   * Accept an array either is a complete item from WC, or a metadata of item.
   * Judge the type by their keys
   * May return null
   *
   * @param array $rawItem
   * @return OrderItemInterface | null
   */
  public function create(array $rawItem): OrderItemInterface|null
  {
    // If $rawItem has key: "id" , "key", "value", it is option item
    if (isset($rawItem['name']) && isset($rawItem['value']) && isset($rawItem['price'])) {
      return new WooCommerceOrderOptionItemAdapter($rawItem);
    }

    // If $rawItem has key: "id", "meta_data"
    if (isset($rawItem['id']) && isset($rawItem['meta_data']) && isset($rawItem['name'])) {
      return new WooCommerceOrderItemAdapter($rawItem);
    }

    return null;
  }
}