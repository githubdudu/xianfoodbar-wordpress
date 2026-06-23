<?php

namespace App\Adapter;

interface OrderItemInterface
{
  public function getMenuNum(): int;
  public function getQuantity(): int;
  public function getUnitPrice(): string;
  public function getNote(): string;
}