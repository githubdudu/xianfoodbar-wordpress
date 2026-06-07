<?php

namespace App\Service;

use App\Model\Menu;
use App\Model\Order;
use App\Model\OrderDetail;

class RemoteOrderDetail
{
  private Order $order;
  private array $products;
  private array $menuList = [];

  public function __construct(Order $order, array $products)
  {
    $this->order = $order;
    $this->products = $products;
  }

  public function saveOrderDetails()
  {
    foreach ($this->products as $product) {
      if (isset($product['product_id']) && !empty($product)) {
        $note = $this->resolveNote($product);

        $menuInfo = Menu::where('out_site_id', $product['product_id'])->first();
        if ($menuInfo) {
          $this->menuList[] = [
            'oid' => $this->order->oid,
            'menu_id' => $menuInfo->id,
            'menu_name' => $menuInfo->menu_name,
            'total' => $product['quantity'],
            'total_price' => $product['subtotal'],
            'add_time' => new \DateTime(),
            'note' => $note
          ];
        }
      }
    }

    foreach ($this->menuList as $product) {
      $orderDetail = new OrderDetail();
      $orderDetail->oid = $product['oid'];
      $orderDetail->menu_id = $product['menu_id'];
      $orderDetail->menu_name = $product['menu_name'];
      $orderDetail->total = $product['total'];
      $orderDetail->total_price = $product['total_price'];
      $orderDetail->add_time = $product['add_time'];
      $orderDetail->note = $product['note'];
      $orderDetail->setPrice();
      $orderDetail->save();
    }
  }

  public function resolveNote(array $product): string
  {
    $note = '';

    foreach (($product['meta_data'] ?? []) as $extra) {
      /**
      * The $extra['value'] can be a string or an array
      *
      *  "meta_data": [
      *         {
      *             "id": 83246,
      *             "key": "302.加大Extra Large (+&#36;1.50)",
      *             "value": "L"
      *         },
      *         {
      *             "id": 83247,
      *             "key": "_exoptions",
      *             "value": [
      *                 {
      *                     "name": "302.加大Extra Large",
      *                     "value": "L",
      *                     "type_of_price": "",
      *                     "price": 1.5,
      *                     "_type": ""
      *                 }
      *             ]
      *         }
      *     ]
      *  Examples return :
      *     74.加大Extra Large (+&#36;4.00) -> L
      *     加菜，蛋，肉，面 Extras (+&#36;2.50) -> 130.加菜.../份Extra Vegs
      * 
      *  Looks like the code is wrong. It should deal with $extra['key'] which contains the encoded
      *  html code, instead of $extra['value']. 
      *  So what we should have got $value to be the price
      */ 
      if (is_string($extra['value'])) {
        $value = explode('&#', html_entity_decode($extra['value'], ENT_HTML5));

        if (count($value) <= 1) {
          $value = explode('$', $extra['value']);
        }

        $note .= $extra['key'] . ' -> ' . trim($value[0], ' +') . "\n";
      }
    }
    return $note;
  }
}
