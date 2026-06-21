<?php

namespace App\Adapter;

interface OrderItemListFactoryInterface
{
  /**
   * @param array $rawItems
   * @return OrderItemInterface[]
   */
  public function createList(array $rawItems): array;
}