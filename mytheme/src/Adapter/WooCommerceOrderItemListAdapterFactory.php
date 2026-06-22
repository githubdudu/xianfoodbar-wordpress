<?php

namespace App\Adapter;

/**
 * A factory class.
 * Take the raw Items array and dispatch them to WooCommerceOrderItemAdapterFactory
 */
class WooCommerceOrderItemListAdapterFactory implements OrderItemListFactoryInterface
{
  /**
   * May contain null value
   *
   * @var OrderItemInterface[]
   */
  private array $orderItemList = [];


  /**
   * Take the raw Items array and dispatch them to WooCommerceOrderItemAdapterFactory
   * Items here can be seen as two level tree structure.
   *
   * @param array $rawItems
   * @return OrderItemInterface[]
   */
  public function createList(array $rawItems): array
  {
    $wcOrderItemAdapterFactory = new WooCommerceOrderItemAdapterFactory();

    foreach ($rawItems as $item) {
      if (empty($item) || !isset($item['name'])) {
        continue;
      }

      // Create the items
      $this->orderItemList[] = $wcOrderItemAdapterFactory->create($item);

      // Create the items hidden in meta
      foreach ($item['meta_data'] as $option_items) {
        // Find '_exoptions'
        if (isset($option_items['_exoptions'])) {

          foreach ($option_items['_exoptions'] as $option_item) {

            $this->orderItemList[] = $wcOrderItemAdapterFactory->create($option_item);

          }
        }
      }
    }

    // There are nullish values , may need filter todo
    return $this->orderItemList;
  }
}