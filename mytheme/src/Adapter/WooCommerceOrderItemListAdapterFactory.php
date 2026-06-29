<?php

namespace App\Adapter;

/**
 * A factory class.
 * Take the raw Items array and dispatch them to WooCommerceOrderItemAdapterFactory
 */
class WooCommerceOrderItemListAdapterFactory
{
  /**
   * May contain null value
   *
   * @var OrderItemInterface[]
   */
  private array $orderItemList = [];


  /**
   * @param OrderItemFactoryInterface $orderItemFactory
   */
  public function __construct(private readonly OrderItemFactoryInterface $orderItemFactory)
  {

  }

  /**
   * Take the raw Items array and dispatch them to WooCommerceOrderItemAdapterFactory
   * Items here can be seen as two level tree structure.
   *
   * @param array $rawItems
   * @return OrderItemInterface[]
   */
  public function createList(array $rawItems): array
  {

    foreach ($rawItems as $item) {
      if (empty($item) || !isset($item['name'])) {
        continue;
      }

      // Create the items
      $this->orderItemList[] = $this->orderItemFactory->create($item);

      // Create the items hidden in meta
      foreach ($item['meta_data'] as $option_items) {
        // Find '_exoptions'
        if ($option_items['key'] === "_exoptions") {

          foreach ($option_items['value'] as $option_item) {

            $this->orderItemList[] = $this->orderItemFactory->create($option_item);

          }
        }
      }
    }

    // There are nullish values , filter out them.
    return array_values(array_filter($this->orderItemList));
  }
}