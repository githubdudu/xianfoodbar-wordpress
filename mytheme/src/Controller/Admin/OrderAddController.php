<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Model\Desk;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminLogger;
use App\Service\AdminMenuGenerator;
use App\Service\AdminMessage;
use Symfony\Component\Routing\Annotation\Route;

class OrderAddController extends Wordpress
{
  public function initAdminMenu(AdminMenuGenerator $menu)
  {
    if ($this->isLogin()) {
      // $menu->addMenus($this->generateTableArray('orderList', 'list'), "订单列表");
      // $menu->addMenus('admin_order_add', '创建订单', [
      //     'name' => 'UnorderedListOutlined'
      // ], useRouter: true);
    }
  }

  #[Route('/adminpanel/order/add/view', name: 'admin_order_add')]
  public function list()
  {
    return $this->render('admin/orderAdd.html.twig', [
      'title' => '创建订单',
      'siteConfig' => [],
    ]);
  }

  #[Route("/api/admin/addTakewayOrder", name: "admin_version_send_takeway_order_by_mobile")]
  public function sendOrders(AdminMessage $message, AdminLogger $logger)
  {
    if ($this->isLogin()) {
      $desk_id = $this->request->request->get('desk_id', 0);
      $phone = $this->request->request->get('phone', '');
      $realname = $this->request->request->get('realname', '');
      $address = $this->request->request->get('address', '');
      $note = $this->request->request->get('note', '');
      $all_price = $this->request->request->get('all_price', '0.00');
      $menu_order = $this->request->request->all('menu_order') ?: [];
      $is_delivery = $this->request->request->get('is_delivery', 0);
      $is_vat_exempt = $this->request->request->get('is_vat_exempt', "");
      $delivery_order_date = $this->request->request->get('delivery_order_date', "");
      $delivery_order_date_time = $this->request->request->get('delivery_order_date_time', "");

      if ($all_price < 0.01 || empty($menu_order)) {
        return $this->sendJson('请先点菜', 500,);
      }

      if ($desk_id <= 0) {
        return $this->sendJson('未知桌号', 404,);
      }

      $desk = Desk::find($desk_id);
      if (!$desk) {
        return $this->sendJson('未知桌号', 404);
      }




      $order = new Order();
      $order->generateOrderSN();
      // $order->create_time = new \DateTime();
      $order->address = $address;
      $order->phone = $phone;
      $order->realname = $realname;
      $order->note = $note;
      $order->is_delivery = $is_delivery;
      $order->is_vat_exempt = $is_vat_exempt;
      $order->delivery_order_date = strval(($delivery_order_date == '' ? date('Y-m-d') : $delivery_order_date) . ' ' . $delivery_order_date_time);
      $order->pay_price = $all_price;
      $order->is_takeway = $desk->is_takeway;
      $order->desk_id = $desk->id;
      $order->is_checked = 1;

      $deskOrderCount = Order::where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0])
        ->where('order_status', '<', 2)
        ->where('create_time', '>', new \DateTime('-10 days'))
        ->count();
      if ($deskOrderCount >= 1) {
        $lastCount = Order::where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0, 'is_pin' => 1])
          ->whereIn('order_status', [0, 1])
          ->where('create_time', '>', new \DateTime('-10 days'))
          ->count();

        if ($lastCount >= 1) {
          $last = Order::where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0, 'is_pin' => 1])
            ->whereIn('order_status', [0, 1])
            ->orderBy('pin_num', 'desc')
            ->first();
          $deskOrderCount = $last->pin_num + 1;
        }

        $order->is_pin = 1;
        $order->pin_num = $deskOrderCount;
      }

      if ($order->save()) {
        if ($order->is_takeway > 0) {
          $message->addMessage('订单通知', '有新的外卖订单', musicFile: $this->getOption('takeway_type2_audio'));
        } else {
          $message->addMessage('订单通知', '有新订单', musicFile: $this->getOption('desk_audio'));
        }
        $oid = $order->oid;
        foreach ($menu_order as $data) {
          $orderDetail = new OrderDetail();
          $orderDetail->oid = $oid;
          $orderDetail->menu_id = $data['mid'];
          $orderDetail->menu_name = $data['name'];
          $orderDetail->total = $data['total'];
          $orderDetail->total_price = $data['price'];
          $orderDetail->add_time = new \DateTime();
          $orderDetail->setPrice();
          $orderDetail->save();
        }

        // 将桌位的状态设为已使用
        $desk->use_status = 1;
        $desk->update();

        $this->addJsonData('data', [
          'oid' => $order->oid
        ]);
        $this->addJsonData('links', $this->generateUrl('admin_system_tabs_show', [
          'name' => 'OrderSystem',
          'method' => 'list',
        ]));
        return $this->sendJson('创建完成', 200,);
      }
      return $this->sendJson('添加失败', 500);
    }

    return $this->sendJson('没有权限访问', 403,);
  }
}
