<?php

namespace App\Controller\Admin;

use App\Core\Controller\SSECore;
use App\Core\Controller\Wordpress;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminMenuGenerator;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class CookController extends Wordpress
{
  private int $checkedCookOrder = -1;
  private int $timer = 5;
  private int $count_time = 0;

  public function initAdminMenu(AdminMenuGenerator $menu)
  {
    $menu->addMenus('admin_back_cook_menu', '后厨系统', useBlank: true, index: 100);
  }

  #[Route('/adminpanel/back/menus', name: 'admin_back_cook_menu')]
  public function menuData()
  {
    return $this->render(
      'admin/cook.html.twig',
      [
        'title' => '后厨系统',
        'active_order' => $this->getOption('active_order') ?? '#000',
        'new_active_order' => $this->getOption('new_active_order') ?? '#000',
        'add_active_order' => $this->getOption('add_active_order') ?? '#000',
        'td_alert' => false,
        'bigFonts' => $this->getOption('big_fonts') ?? 34,
      ],
    );
  }

  private function getUncookedOrders(): int
  {

    $allOrder = Order::with('details')->where([
      'is_cancel' => 0,
      'is_delete' => 0,
      'is_checked' => 1,
    ])
      ->where('create_time', '>', new DateTime('-10 day'))
      ->whereIn('order_status', [0, 1])
      ->get();
    $count = 0;
    foreach ($allOrder as $order) {
      foreach ($order->details as $detail) {
        if ($detail->is_delete == 1) {
          continue;
        }
        $count += $detail->total - $detail->add_count;
      }
    }

    return $count;
  }

  /**
   * 获取后厨的数据
   */
  #[Route('/api/admin/menu_info', name: 'admin_menu_info')]
  public function getMenu(): \Symfony\Component\HttpFoundation\StreamedResponse
  {

    return SSECore::createResponse(function () {
      echo "  ";
      if ($this->count_time >= 30) {
        $this->count_time = 0;
        $this->checkedCookOrder = -1;
      }

      // 获取待烹饪订单没有上菜的数量

      $uncookedOrders = $this->getUncookedOrders();
      if ($this->checkedCookOrder != $uncookedOrders) {
        $this->checkedCookOrder = $uncookedOrders;
        $this->count_time = -1;
        return SSECore::createDataArray('message', $this->getData2());
      } else {
        // $new = $table->table('res_order_detail')->orderBy('add_time', 'desc')->first();
        // $new = DateTime::createFromFormat('Y-m-d H:i:s', $new->add_time);
        // $now = new DateTime();
        // $now->modify('-60 second');
        // if ($new > $now) {
        //     return SSECore::createDataArray('message', $this->getData2());
        // }
      }
      $this->count_time += $this->timer;

      // $data = json_encode($this->getData());

      // return [
      //     'event' => 'message',
      //     'data' => $data,
      // ];
    }, interval: $this->timer, retry: 500, max: 5);
  }

  private function getDeskKey(Order $order): string
  {
    $id = $order->desk_id;
    return 'd_' . $id . '_' . $order->oid;  // ($desk->getIsTakeway() == 0  ? '' : );
  }

  private function getFontSize(mixed $font, float|int $plus = 2)
  {
    $fontLength = mb_strlen($font);
    $fontSize = $this->getOption('big_fonts', 18);
    $fontSize = $fontSize >= 18 ? $fontSize : 18;
    return ($fontLength + $plus) * $fontSize;
  }

  public function getData2(): array
  {
    $orders = Order::with('desk', 'details.menu')->where('create_time', '>', new DateTime("-10 day"))
      ->whereIn('order_status', [0, 1])
      ->where('is_cancel', 0)
      ->where('is_delete', 0)
      ->where('is_checked', 1)
      //->orderBy('is_takeway', 'asc')
      ->orderBy('create_time', 'asc')
      ->get();
    //$desks = Database::

    $desk_list = [
      [
        'title' => '0',
        'dataIndex' => 'menu_name',
        // 'key' => 'menu_name',
        'width' => $this->getOption('big_fonts') * 4,
        'align' => 'center',
        'fixed' => 'left',
        'className' => 'bigFont td_bold is_menu'
      ]
    ];

    $showMenu = [];
    $deskNameId = 1;
    foreach ($orders as $order) {
      if ($order->order_status == 2) {
        continue;
      }
      //if (isset($hasDesk[$this->getDeskKey($order)]) && $order->id == $hasDesk[$this->getDeskKey($order)]) {
      //    continue;
      //}
      $desk = $order->desk;
      // var_dump($desk->menu_guid);
      $id = $order->desk_id;
      $pin = $order->pin_num ?: $order->is_pin;
      $createTime = $order->create_time;
      $className = ($order->is_takeway == 0 && !empty($order->note) ? ' td_alert_note' : '')
        . (abs(time() - $createTime->getTimestamp()) < 60 ? ' new_alert' : '');

      $className .= ' ' . ($order->is_pin > 0 ? ' new_pin_order' : '');
      $title = ($desk->menu_guid ?? $id);
      if ($order->is_takeway == 0) {
        $title .= ($pin > 0 ? '-' . $pin : '');
      } else {
        $title += max($pin, 0);
      }
      $dataKey = $this->getDeskKey($order);
      $desk_list['desk_' . $deskNameId] = [
        'title' => $title,
        'dataIndex' => $dataKey,
        // 'key' => $this->getDeskKey($order),
        'width' => $this->getFontSize('', 2),
        'align' => 'center',
        'className' => 'bigFont td_bold' . $className
      ];

      $deskIndex = 'desk_' . $deskNameId;
      $allCount = 0;

      foreach ($order->details as $detail) {
        if ($detail->is_delete == 1) {
          continue;
        }
        $count = $detail->total - $detail->add_count;
        $allCount += $count;
        if ($count <= 0) {
          continue;
        }
        $menu = $detail->menu;
        $showMenu[$menu->menu_name]['menu_id'] = $menu->id;
        $showMenu[$menu->menu_name]['selected_menu_id'] = 0;

        // ($order->getOrder()->getIsTakeway() == 0  ? '' : $extends);
        $addTime = new DateTime($detail->add_time);
        if (time() - $addTime->getTimestamp() <= 60) {
          // $elementRoot = $this->search($desk_list, $dataKey, 'dataIndex');
          // if (!str_contains($desk_list[intval($elementRoot)]['className'], 'new_alert')) {
          $desk_list[$deskIndex]['className'] .= ' add_new_menu ';
          // }
          $showMenu[$menu->menu_name]['selected_menu_id'] = $menu->id;
        }

        if (!isset($showMenu[$menu->menu_name][$dataKey])) {
          $showMenu[$menu->menu_name][$dataKey] = $count ?: '';
        } else {
          $showMenu[$menu->menu_name][$dataKey] += $count;
        }

        if (mb_strlen($menu->menu_name) > 1) {
          $width = $this->getFontSize($menu->menu_name, 0.5);
          //echo $width . ' , ' . $desk[0]['width'];
          if ($desk_list[0]['width'] < $width) {
            $desk_list[0]['width'] = $width;
          }
        }
      }

      if ($allCount <= 0) {
        unset($desk_list[$deskIndex]);
        //$desk_list[$deskIndex]['count'] = $allCount;
      }

      $deskNameId += 1;
      //$hasDesk[$this->getDeskKey($order)] = $order->oid;
      //$desk_ids[$desk->id] = isset($desk_ids[$desk->id]) ? $desk_ids[$desk->id] + 1 : 1;
    }

    $newDesk = [];

    foreach ($desk_list as  $value) {
      $name = $value['dataIndex'];
      if ($name === 'menu_name') {
        $value['title'] = '菜名';
        $newDesk[] = $value;
        continue;
      }

      $newDesk[] = $value;
    }

    if (count($newDesk) < 21) {
      $count = count($newDesk);
      for ($i = 0; $i < 21 - $count; $i++) {
        $newDesk[] = [
          'title' => '',
          'dataIndex' => 'dddd',
          'key' => 'menu_name',
          'width' => 140,
          'align' => 'center',
          'className' => 'bigFont td_bold'
        ];
      }
      unset($count);
    }

    $menuList = [];
    foreach ($showMenu as $menu => $menuValue) {
      $menuList[] = [
        'menu_name' => $menu,
        ...$menuValue
      ];
    }

    return [
      'column' => [
        ...$newDesk
      ],
      'data' => $menuList,
    ];
  }

  // public function getData(): array
  // {
  //   $desk_list = [
  //     [
  //       'title' => '菜名',
  //       'dataIndex' => 'menu_name',
  //       'key' => 'menu_name',
  //       'width' => $this->getOption('big_fonts') * 5 * 0.95,
  //       'align' => 'center',
  //       'fixed' => 'left',
  //       'className' => 'bigFont td_bold is_menu'
  //     ]
  //   ];
  //   //        $hasDesk = [];
  //   //        $hasDesk2 = [];
  //   //        $baseDesk = [];
  //   //        $hasIndex = [];
  //   //        $deskDataIndexList = [];
  //
  //   foreach ($deskList as $desk) {
  //     $id = $desk->desk_id;
  //     $pin = $desk->pin_num ?: $desk->is_pin;
  //     try {
  //       $createTime = $desk->create_time;
  //     } catch (\Exception) {
  //       $createTime = 0;
  //     }
  //     $className = ($desk->is_takeway == 0 && !empty($desk->note) ? ' td_alert_note' : '')
  //       . (abs(time() - $createTime->getTimestamp()) < 60 ? ' new_alert' : '');
  //
  //     $className .= ' ' . ($desk->is_pin > 0 ? ' new_pin_order' : '');
  //     $title = ($desk->menu_guid ?? $id);
  //     if ($desk->is_takeway == 0) {
  //       $title .= ($pin > 0 ? '-' . $pin : '');
  //     } else {
  //       $title += max($pin, 0);
  //     }
  //
  //
  //     $desk_list[] = [
  //       'title' => $title,
  //       'dataIndex' => $this->getDeskKey($desk),
  //       'key' => $this->getDeskKey($desk),
  //       'width' => $this->getFontSize($title . '_'),
  //       'align' => 'center',
  //       'className' => 'bigFont td_bold' . $className
  //     ];
  //   }
  //
  //   $showMenu = [];
  //   $tempMenus = ['menu_name'];
  //   $noHasMenu = [];
  //
  //   foreach ($menuList as $menu) {
  //     $menuName = $menu->menu_name;
  //
  //     $temp = [];
  //     $temp['menu_id'] = $menu->menu_id;
  //     $temp['selected_menu_id'] = 0;
  //     $menu_count = 0;
  //
  //     foreach ($orderList as $order) {
  //       if ($order->menu_name == $menuName) {
  //         $count = $order->total - $order->add_count;
  //         $dataKey = $this->getDeskKey($order);  // ($order->getOrder()->getIsTakeway() == 0  ? '' : $extends);
  //         $addTime = DateTime::createFromFormat('Y-m-d H:i:s', $order->add_time);
  //         if (time() - $addTime->getTimestamp() <= 60) {
  //           $elementRoot = $this->search($desk_list, $dataKey, 'dataIndex');
  //           $desk_list[intval($elementRoot)]['className'] .= ' add_new_menu ';
  //           $temp['selected_menu_id'] = $menu->menu_id;
  //         }
  //         $tempMenus[] = $dataKey;
  //
  //         if (!isset($noHasMenu[$dataKey])) {
  //           $noHasMenu[$dataKey] = $count;
  //         } else {
  //           $noHasMenu[$dataKey] += $count;
  //         }
  //
  //         if (!isset($temp[$dataKey])) {
  //           $temp[$dataKey] = $count ?: '';
  //         } else {
  //           $temp[$dataKey] += $count;
  //         }
  //
  //         $menu_count += $count;
  //       }
  //     }
  //
  //     if ($menu_count > 0) {
  //       $temp['menu_name'] = $menu->menu_name;
  //       // var_dump($menu->getMenuName(), mb_strlen($menu->getMenuName()));
  //       if (mb_strlen($menu->menu_name) > 4) {
  //         $width = $this->getFontSize($menu->menu_name);
  //         if ($desk_list[0]['width'] < $width) {
  //           $desk_list[0]['width'] = $width;
  //         }
  //       }
  //       $showMenu[] = $temp;
  //     }
  //   }
  //
  //   $newDesk = [];
  //   foreach ($desk_list as  $value) {
  //     $name = $value['dataIndex'];
  //
  //     if ((isset($noHasMenu[$name]) && $noHasMenu[$name] <= 0) || !in_array($name, $tempMenus)) {
  //       continue;
  //     }
  //
  //     $newDesk[] = $value;
  //   }
  //
  //   if (count($newDesk) < 21) {
  //     $count = count($newDesk);
  //     for ($i = 0; $i < 21 - $count; $i++) {
  //       $newDesk[] = [
  //         'title' => '',
  //         'dataIndex' => 'dddd',
  //         'key' => 'menu_name',
  //         'width' => 140,
  //         'align' => 'center',
  //         'className' => 'bigFont td_bold'
  //       ];
  //     }
  //     unset($count);
  //   }
  //
  //   return [
  //     'column' => $newDesk,
  //     'data' => $showMenu,
  //   ];
  // }

  private function search(array $array, string $search, string $key = null)
  {
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        $result = $this->search($v, $search, $key) ?: null;
        if ($result !== null) {
          return $k;
        }
        continue;
      }

      if (!empty($key) && $k == $key) {
        if ($v == $search) {
          return $k;
        }
      }

      if ($v == $search) {
        return $k;
      }
    }
  }
}
