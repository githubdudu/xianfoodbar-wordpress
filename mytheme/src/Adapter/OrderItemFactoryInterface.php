<?php

namespace App\Adapter;

interface OrderItemFactoryInterface
{
  public function create(array $rawItem): OrderItemInterface | null;
}