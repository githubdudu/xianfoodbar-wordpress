<?php

namespace App\Service;

use App\Model\Desk;
use App\Model\Menu;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminMessage;
use Symfony\Component\Routing\Annotation\Route;

class RemoteOrderDetail
{
  public static function saveOrderDetails(Order $order, array $products)
  {
    $menuList = [];
    foreach ($products as $product) {
      if (isset($product['product_id']) && !empty($product)) {
        $note = '';
        if (count($product['meta_data']) > 0) {

          foreach ($product['meta_data'] as $data2) {
            if (is_string($data2['value'])) {
              $value = explode('&#', html_entity_decode($data2['value'], ENT_HTML5));
              if (count($value) <= 1) {
                $value = explode('$', $data2['value']);
              }
              $note .= $data2['key'] . ' -> ' . trim($value[0], ' +') . "\n";
            }
          }
        }
        $menuInfo = Menu::where('out_site_id', $product['product_id'])->first();
        if ($menuInfo) {
          $menuList[] = [
            'oid' => $order->oid,
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

    foreach ($menuList as $product) {
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
}