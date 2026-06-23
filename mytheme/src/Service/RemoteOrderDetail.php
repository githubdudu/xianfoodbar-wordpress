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
  /**
   * @var array<int, array{
   *   id: int,
   *   order_id: int,
   *   name: string,
   *   product_id: int,
   *   variation_id: int,
   *   quantity: int,
   *   tax_class: string,
   *   subtotal: string,
   *   subtotal_tax: string,
   *   total: string,
   *   total_tax: string,
   *   taxes: array{
   *     total: array,
   *     subtotal: array,
   *   },
   *   meta_data: array<int, array{
   *     id: int,
   *     key: string,
   *     value: string | array,
   *   }>,
   * }> $items
   */
  private array $items;
  private array $menuList = [];
  private LoggerInterface $logger;

  private array $upgradeMenuList = [
    37 => [
      'members' => [38, 370],
      'combos' => ['38, 370' => 380]
    ],
    301 => [
      'members' => [51, 302],
      'combos' => ['51, 302' => 3020]
    ]
  ];

  /**
   * @param int $oid
   * @param array<int, array{
   *   id: int,
   *   order_id: int,
   *   name: string,
   *   product_id: int,
   *   variation_id: int,
   *   quantity: int,
   *   tax_class: string,
   *   subtotal: string,
   *   subtotal_tax: string,
   *   total: string,
   *   total_tax: string,
   *   taxes: array{
   *     total: array,
   *     subtotal: array,
   *   },
   *   meta_data: array<int, array{
   *     id: int,
   *     key: string,
   *     value: string | array,
   *   }>,
   * }> $items
   */
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

      // This is the num of the menu in the local store
      $menu_num = $this->getMenuNumFromItemName($item['name']);

      $note = $this->resolveNote($item);
      $real_menu_num = $this->checkUpgradeOption($item['meta_data'], $menu_num);

      // The out_site_id is the connection between the online order and the local menu. 
      // It is set when the menu is created and set to be equal to product_id from the database of WooCommerce.
      // removed out_site_id query, with menu_num: like 37.
      $menuInfo = Menu::where('menu_num', $real_menu_num)->first();

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

  /**
   * @param array<int, array{
   *   id: int,
   *   key: string,
   *   value: string | array,
   * }> $meta_data
   * @param int $original_menu_num
   */
  public function checkUpgradeOption(array $meta_data, int $original_menu_num): int
  {
    $key_list = [];
    foreach ($meta_data ?? [] as $extra) {
      if (!is_string($extra['value'])) {
        continue;
      }
      $menu_num = $this->getMenuNumFromItemName($extra['key']);
      if ($menu_num) {
        $key_list[] = $menu_num;
      }
    }

    // Filter out by $this->upgradeMenuList[$original_product_id]['members']
    $key_list = array_intersect($key_list, $this->upgradeMenuList[$original_menu_num]['members']);

    // Count == 0
    if (count($key_list) == 0) {
      return $original_menu_num;
    }

    // Count == 1
    if (count($key_list) == 1) {
      return $key_list[0];
    }

    // Count == 2
    // Sort and join the key list
    sort($key_list);
    $combo_key = implode(', ', $key_list);

    return $this->upgradeMenuList[$original_menu_num]['combos'][$combo_key] ?? $original_menu_num;
  }

  /**
   * @param string $name the string we get from name of order item
   *
   * @return int menu_id This is the id of the menu in the local store
   */
  public function getMenuNumFromItemName(string $name): int
  {
    return intval($name);
  }
}
