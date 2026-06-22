<?php

namespace App\Adapter;

/**
 * The adapter for explicit items in order items.
 * It has all info
 */
class WooCommerceOrderItemAdapter implements OrderItemInterface
{
  /**
   * @var array{
   *   id: int,
   *   order_id: int,
   *   name: string,
   *   quantity: int,
   *   subtotal: string,
   *   subtotal_tax: string,
   *   total: string,
   *   total_tax: string,
   *   taxes: array,
   *   meta_data: array{
   *     int, array{
   *       id: int,
   *       key: string,
   *       value: string | array,
   *     },
   *   }
   * }
   */
  protected array $rawItem;
  private int $realMenuNum;
  private string $note;

  static private array $upgradeMenuList = [
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
   * @param array{
   *    id: ?int,
   *    order_id: ?int,
   *    name: string,
   *    quantity: ?int,
   *    subtotal: ?string,
   *    meta_data: ?array{
   *      int, array{
   *        id: int,
   *        key: string,
   *        value: string | array,
   *      },
   *    }
   *  } $rawItem
   */
  public function __construct(array $rawItem)
  {
    $this->rawItem = $rawItem;

    $itemNameWithMenuNum = $this->getMenuNumString();
    $menu_num = $this->getMenuNumFromItemName($itemNameWithMenuNum);
    $this->realMenuNum = $this->checkUpgradeOption($rawItem['meta_data'], $menu_num);

    $this->note = $this->resolveNote($rawItem['meta_data']);
  }


  /**
   * @return int
   */
  public function getMenuNum(): int
  {
    return $this->realMenuNum;
  }

  /**
   * @return int
   */
  public function getQuantity(): int
  {
    return $this->rawItem['quantity'];
  }

  /**
   * @return string
   */
  public function getUnitPrice(): string
  {
    return $this->rawItem['subtotal'];
  }

  /**
   * @return string
   */
  public function getNote(): string
  {
    return $this->note;
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

  /**
   * @param array<int, array{
   *   id: int,
   *   key: string,
   *   value: string | array,
   * }>|null $meta_data
   * @param int $original_menu_num
   * @return int
   */
  public function checkUpgradeOption(?array $meta_data, int $original_menu_num): int
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
    $key_list = array_intersect($key_list, self::$upgradeMenuList[$original_menu_num]['members']);

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

    return self::$upgradeMenuList[$original_menu_num]['combos'][$combo_key] ?? $original_menu_num;
  }


  /**
   * @param array<int, array{
   *    id: int,
   *    key: string,
   *    value: string | array,
   *  }>| null $meta_data
   * @return string
   */
  public function resolveNote(?array $meta_data): string
  {
    $note = '';

    foreach (($meta_data ?? []) as $extra) {
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
   * Get a string like this "132.加肉.../份Extra Meat"
   *
   * @return string
   */
  public function getMenuNumString(): string
  {
    return $this->rawItem['name'];
  }

}