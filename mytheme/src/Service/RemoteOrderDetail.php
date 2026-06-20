<?php

namespace App\Service;

use App\Model\Menu;
use App\Model\Order;
use App\Model\OrderDetail;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RemoteOrderDetail
{
  private int $oid;
  private array $items;
  private array $menuList = [];
  private LoggerInterface $logger;

  public function __construct(int $oid, array $items, ?LoggerInterface $logger = null)
  {
    $this->oid = $oid;
    $this->items = $items;
    $this->logger = $logger ?? new NullLogger();
  }

  public function saveOrderDetails()
  {
    foreach ($this->items as $item) {
      if (empty($item) || !isset($item['product_id'])) {
        continue;
      }

      $note = $this->resolveNote($item);

      // The out_site_id is the connection between the online order and the local menu. 
      // It is set when the menu is created. 
      // The product_id is from database of WooCommerce.
      $menuInfo = Menu::where('out_site_id', $item['product_id'])->first();

      if (!$menuInfo) {
        $this->logger->error('product_id' . $item['product_id'] . ' not found');
        continue;
      }

      $orderDetail = new OrderDetail();

      $orderDetail->oid = $this->oid;  // order id: The foreign key of the orderDetail
      $orderDetail->menu_id = $menuInfo->id;
      $orderDetail->menu_name = $menuInfo->menu_name;
      $orderDetail->total = $item['quantity'];
      $orderDetail->total_price = $item['subtotal']; // total_price is a bad naming. 单价 is the price of a single item
      $orderDetail->add_time = new \DateTime();
      $orderDetail->note = $note;
      $orderDetail->setPrice(); // total * total_price

      $orderDetail->save();
    }
  }

  public function resolveNote(array $item): string
  {
    $note = '';

    foreach (($item['meta_data'] ?? []) as $extra) {
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
      if (!is_string($extra['value'])) {
        continue;
      }

      $value = explode('&#', html_entity_decode($extra['value'], ENT_HTML5));

      if (count($value) <= 1) {
        $value = explode('$', $extra['value']);
      }

      $note .= $extra['key'] . ' -> ' . trim($value[0], ' +') . "\n";
    }

    return $note;
  }
}
