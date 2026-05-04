<?php

namespace App\Controller\Admin;

use App\Core\Controller\SSECore as ControllerSSECore;
use App\Core\Controller\Wordpress;
use App\Core\DateTime;
use App\Core\StylesLayout;
use App\Core\TabsData;
use App\Core\TabsDataList;
use App\Core\TabsLayout;
use App\Model\Desk;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminMenuGenerator;
use Symfony\Component\Routing\Annotation\Route;

class OrderSystemController extends Wordpress
{
  public function initAdminMenu(AdminMenuGenerator $menu)
  {
    if ($this->isLogin()) {
      $menu->addMenus($this->generateTabsArray('OrderSystem', 'list'), '订单系统', useRouter: true, index: 0);
    }
  }

  public function list()
  {
    $tabsConfig = new TabsLayout('订单实时系统', '显示最新的上桌数据');
    $tabsConfig->addTabs(
      '餐厅桌位',
      'desk',
      $this->router->generate('get_new_orderSystem_list', []),
      badge: 0,
      isLongRequest: true,
    )->addTabs(
      '外卖',
      'takeway',
      $this->router->generate('get_new_orderSystem_list', ['type' => 1]),
      isLongRequest: true,
      badge: 0,
    );
    return $this->tabsTemplate($tabsConfig);
  }

  #[Route('/api/admin/orderSystem/list/{type}', defaults: ['type' => 0], name: 'get_new_orderSystem_list')]
  #[Route('/api/admin/orderSystem/list', name: 'get_new_orderSystem_list2')]
  public function getData(int $type = 0)
  {
    if ($this->isLogin()) {
      $timelimit = 2;
      $runtime = 0;
      $closestep = 0;
      $checkedOrder = -1;
      $checkedOrder = -1;
      $checkPoint = -2;

      return ControllerSSECore::createResponse(function () use (&$runtime, $timelimit, &$checkedOrder, &$closestep, &$checkPoint) {
        if ($runtime >= 60) {
          $runtime = 0;
          $closestep += 1;
          $checkedOrder = -1;
        }

        if ($closestep >= 3) {
          $closestep = 1;
          return ControllerSSECore::createDataArray('close', []);
        }

        $orders = Order::where('is_cancel', 0)
          ->where('create_time', '>', new DateTime("-10 days"))
          ->whereIn('order_status', [0, 1])
          ->where('is_delete', 0)
          ->count();

        if ($orders != $checkedOrder) {
          $checkedOrder = $orders;
          return ControllerSSECore::createDataArray('message', $this->getList());
        }

	$checkPoint += 1;
        $runtime += $timelimit;
      }, 'message', $timelimit, 500, 3);
    }

    return $this->sendJson('没有权限', 403);
  }

  private function getList(): array
  {
    //        $orderList = Order::where([
    //            'is_delete' => 0,
    //            'is_cancel' => 0,
    //            'is_takeway' => $type,
    //        ])
    //            ->where('order_status', '<', 2)
    //            ->orderBy('desk_id', 'asc')
    //            ->get([
    //                'oid', 'is_read', 'order_status', 'pay_price', 'desk_id', 'is_pin', 'pin_num'
    //            ]);

    $orderList = Order::where('create_time', '>', new DateTime("-10 days"))
      ->where([
        'is_delete' => 0,
        'is_cancel' => 0,
      ])->whereIn('order_status', [0, 1])
      ->orderBy('desk_id', 'asc')->get();
    // ->where('create_time', '>=', $oldTime->format('Y-m-d 00:00:00'))

    /** @var Desk[] $deskList */
    $deskList = Desk::all();

    $deskType = ['desk' => 0, 'takeway' => 1];
    $listJson = [];
    foreach ($deskType as $typeDesk => $Typeval) {
      $notReadCount = 0;
      $countDesk = [];
      $tabsDataList = new TabsDataList($Typeval === 0 ? 'desk' : 'takeway');
      $title = '后厨: ';
      $rootStyle = (new StylesLayout())
        ->setBackgroundColor(StylesLayout::rgba(190, 85, 85, 0.8))
        ->setBorderColor(StylesLayout::Red_6);
      $titleStyle = (new StylesLayout())->setFontColor(StylesLayout::White);
      $contentStyle = $titleStyle;

      $desk_ids = [];
      foreach ($orderList as $order) {
        if ($order->is_takeway != $Typeval) {
          continue;
        }

        $tabsData = new TabsData($this->router->generate('admin_order_info', ['oid' => $order->oid, 'id' => $order->oid]), true);

        if ($order->is_read != 1) {
          $tabsData->setBadgeText('NEW');
          $notReadCount += 1;
        }
        $tabsData
          ->setContent('状态', value: $order->order_status, valueEnums: ['未支付', '已支付'])
          // ->setContent('未上菜数', count: (int)$last_count, stuffer: '道')
          // ->setContent('上菜数', count: (int)$count, stuffer: '道')
          ->setContent('', count: '')
          ->setExtra("总: {$order->pay_price}")
          ->setStyle('root', $rootStyle)
          ->setStyle('content', $contentStyle)
          ->setStyle('title', $titleStyle);

        /** @var OrderDetail[] $orderDetailList */
        $orderDetailList = $order->details;
        foreach ($orderDetailList as $detail) {
          $count = $detail->total;

          if ($count > 0) {
            $tabsData->setContent($detail->menu_name, count: $count);
          }
        }

        foreach ($deskList as $desk) {
          if ($desk->getId() == $order->desk_id) {
            $pinNum = $order->pin_num ?: $order->is_pin;
            $nowTitle = $desk->desk_name;
            if ($order->is_takeway == 1) {
              $nowTitle .= '-' . $desk->menu_guid;
              if ($pinNum > 0) {
                $nowTitle .= '_' . $pinNum;
              }
            } else {
              if ($pinNum > 0) {
                $nowTitle .= '-' . $pinNum;
              }
            }

            $tabsData->setTitle($title . $nowTitle);
            $countDesk[] = $desk->id;
            $desk_ids[$order->desk_id] = isset($desk_ids[$order->desk_id]) ? $desk_ids[$order->desk_id] + 1 : 1;
          }
        }

        $tabsDataList->addData($tabsData);
      }

      if ($Typeval === 0) {
        foreach ($deskList as $desk) {
          if ($desk->is_takeway === 0 && !in_array($desk->id, $countDesk)) {
            $tabsDataList->addData((new TabsData())->setTitle('后厨: ' . $desk->desk_name)->setContent('未使用', isString: true));
          }
        }
      }
      $listJson[$typeDesk] = $tabsDataList->setBadgeText($notReadCount);
    }

    return $listJson;
  }
}
