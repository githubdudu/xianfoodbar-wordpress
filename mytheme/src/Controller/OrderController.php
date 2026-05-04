<?php

namespace App\Controller;

use App\Model\Desk;
use App\Model\Menu;
use App\Model\Order;
use App\Model\OrderDetail;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\AdminMessage;
use App\Core\Controller\Wordpress;

class OrderController extends Wordpress
{
  #[Route('/order/{order_sn}', name: 'order_display')]
  public function orderDisplay()
  {
    return $this->render('index.html.twig', []);
  }

  private function getPrice($menuData)
  {
    $price = 0;
    foreach ($menuData as $menu) {
      // if ($menu[])
      $price += $menu['price'] * $menu['total'];
    }

    return $price;
  }

  /**
   * 创建新订单
   */
  #[Route("/api/order/add", name: "add_new_order2")]
  public function addOrder(AdminMessage $message)
  {
    $canQrcode = $this->getOption('can_qrcode', true);
    if (!$canQrcode) {
      $this->addJsonData('is_always', true);
      return $this->sendJson(
        '请前往前台点餐<br />Please go to the front to order',
        500
      );
    }

    if ($this->request->isMethod('POST')) {
      $desk_id = $this->request->request->get('desk_id', 0);
      $menu_data = $this->request->request->all('menu_data');
      $order_id = $this->request->request->get('order_id', 0);
      $note = $this->request->request->get('note', "");

      if (!empty($order_id)) {
        return $this->addOrder2($message, $order_id, $menu_data, $note);
      }


      $desk = Desk::find($desk_id);
      $deskOrderCount = Order::where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0])
        ->whereIn('order_status', [0, 1])
        ->count();

      $all_price = $this->getPrice($menu_data);
      $orderData = new Order();
      $orderData->generateOrderSN();
      $orderData->pay_price = $all_price;
      $orderData->desk_id = $desk_id;
      $orderData->note = $note;
      $orderData->is_checked = 1;
      // $orderData->create_time = new \DateTime();

      if ($deskOrderCount >= 1) {
        $lastCount = Order::where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0, 'is_pin' => 1])
          ->whereIn('order_status', [0, 1])
          ->count();

        if ($lastCount >= 1) {
          $last = Order::whereIn('order_status', [0, 1])
            ->where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0, 'is_pin' => 1])
            ->orderBy('pin_num', 'desc')->first();
          $deskOrderCount = $last->pin_num + 1;
        }

        $orderData->is_pin = 1;
        $orderData->pin_num = $deskOrderCount;
      }

      if ($orderData->save()) {
        $message->addMessage('订单通知', '有新订单', musicFile: $this->getOption('desk_audio'));
        $oid = $orderData->oid;
        foreach ($menu_data as $key => $data) {
          $orderDetail = new OrderDetail();
          $orderDetail->oid = $oid;
          $orderDetail->menu_id = $data['mid'];
          $orderDetail->menu_name = $data['name'];
          $orderDetail->total = $data['total'];
          $orderDetail->total_price = $data['price'];
          $orderDetail->add_time = new \DateTime();
          $orderDetail->setPrice();
          $orderDetail->save();

          $menu_data[$key]['old_total'] = $orderDetail->total;
          $menu_data[$key]['odid'] = $orderDetail->odid;
        }

        // 将桌位的状态设为已使用
        $desk->use_status = 1;
        $desk->update();

        $this->addJsonData('data', [
          'order_id' => $orderData->order_sn,
          'menu_data' => $menu_data,
        ]);
        return $this->sendJson('创建完成', 200);
      } else {
        return $this->sendJson('未知错误', 500);
      }
    }

    return $this->sendJson('没有找到数据', 404);
  }

  public function addOrder2(AdminMessage $message, $order_sn, $order_detail, $note)
  {

    $nowOrder = Order::where('order_sn', $order_sn)->where('order_status', '<', 2)->first();
    if (empty($nowOrder)) {
      return $this->sendJson("没有找到订单", 404);
    }
    $all_price = $this->getPrice($order_detail);
    $nowOrder->pay_price = $all_price;
    $nowOrder->note = $note;

    if ($nowOrder->update()) {

      $oid = $nowOrder->oid;
      foreach ($order_detail as $key => $item) {
        if ($item['odid'] != 0) {
          $orderDetail = OrderDetail::find($item['odid']);
          if ($orderDetail->total !== $item['total']) {
            $orderDetail->add_time = new \DateTime();
          }
          $orderDetail->is_delete =  $item['is_delete'] ? 1 : 0;
        } else {
          $orderDetail = new OrderDetail();
          $orderDetail->add_time = new \DateTime();
          $orderDetail->add_count = 0;
          $orderDetail->oid = $oid;
          $orderDetail->menu_id = $item['mid'];
          $orderDetail->menu_name = $item['name'];
          $orderDetail->total_price = $item['price'];
        }

        $orderDetail->total = $item['total'];
        $orderDetail->setPrice();
        if ($orderDetail->odid > 0) {
          $orderDetail->update();
        } else {
          $orderDetail->save();
        }


        $order_detail[$key]['old_total'] = $orderDetail->total;
        $order_detail[$key]['odid'] = $orderDetail->odid;
      }

      $message->addMessage('订单修改通知', '有订单加菜', musicFile: $this->getOption('takeway_type3_audio'));
      $this->addJsonData('data', [
        'order_id' => $nowOrder->order_sn,
        'menu_data' => $order_detail
      ]);
      return $this->sendJson('新增完成', 200);
    }
    return $this->sendJson('出现错误', 500);
  }

  /**
   *
   */
  #[Route("/api/order/info/{order_id}", name: "order_info2")]
  public function order($order_id = '')
  {
    if (!empty($order_id)) {
      /**
       * @var order
       */
      $orderInfo = Order::where('order_sn', $order_id)->first();

      if ($orderInfo && $orderInfo->order_status < 2 && $orderInfo->is_delete == 0 && $orderInfo->is_cancel == 0) {
        $orderDetail = OrderDetail::where([
          'oid' => $orderInfo->oid,
          'is_delete' => 0
        ])->get();

        $orderDetailList = [];
        foreach ($orderDetail as $item) {
          $item = $item->toArray();
          $orderDetailList[] = [
            ...$item,
            'menu_id' => Menu::find($item['menu_id']),
          ];
        }
        $orderInfo->order_detail = $orderDetailList;

        $this->addJsonData('data', $orderInfo);
        return $this->sendJson();
      }
    }
    return $this->sendJson('没有找到订单', 404);
  }

  /**
   *
   */
  #[Route("/api/order/check/{order_sn}", name: "check_order_sn2")]
  public function checkSn($order_sn = ''): \Symfony\Component\HttpFoundation\Response
  {
    if (!empty($order_sn)) {

      /**
       * @var Order
       */
      $order = Order::where([
        'order_sn' => $order_sn,
        'is_delete' => 0,
        'is_cancel' => 0,
      ])->where('order_status', '<', 2)->count();

      if ($order > 0) {
        return $this->sendJson('', 200);
      }
    }
    return $this->sendJson('未找到', 404);
  }
}
