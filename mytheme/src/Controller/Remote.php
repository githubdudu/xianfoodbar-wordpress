<?php

namespace App\Controller;

use App\Core\Controller\Wordpress;
use App\Model\Desk;
use App\Model\Menu;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class Remote extends Wordpress
{
  #[Route('/api/remote/getdata', name: 'get_order_data_from_remote')]
  #[Route('/api/remote/getdata/{id}', name: 'get_order_data_from_remote2')]
  public function getData(AdminMessage $message, ?int $id = null)
  {
    if (!$id) {
      return $this->sendJson("", 404);
    }
    // sleep(1);
    $orderData = $this->request->request->all();
    if (empty($orderData)) {
      return $this->sendJson("", 404);
    }
    file_put_contents(dirname(__DIR__, 2) . '/var/orderdata_' . date('YmdHis') . '.json', json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $phone = $orderData['phone'];
    $realname = $orderData['name'];
    $address = $orderData['address'];
    $note = $orderData['order']['customer_note'];
    $orderkey = $orderData['order']['order_key'];
    $all_price = $orderData['total'];
    $menu_order = $orderData['items'];
    $date = $orderData['order']['date_created']['date'];
    $metas = $orderData['metas'];
    $status = $orderData['status'];

    $time = strtotime($date);
    if ($time < time() - 43200) {
      return $this->sendJson('', 200);
    }

    $is_new = true;
    $is_update = false;
    $desk_id = $this->getOption('site_takeway_did', 0);
    $deskOrderCount = 0;

    $exists = Order::where('takeway_order', 'orderdata_' . $id)->first();
    if ($exists) {
      $is_new = false;
      $order = $exists;
      if ($order->order_status == 2) {
        return $this->sendJson('completed', 200);
      }
    } else {
      $order = new Order();
    }


    $desk = Desk::find($desk_id);
    if ($desk) {
      $desk = Desk::where('is_takeway', 1)->first();
      $desk_id = $desk->id;
    }

    $labels = [
      'is_vat_exempt' => '免增值税',
      '_order_date' => '提取（希望送达）订单日期',
      '_order_time' => '提取（送达）订单时间',
      '_before_checkout_billing_form_pick_up_or_delivery' => '自取还是送餐',
    ];

    // $order->order_status = 0;

    switch ($status) {
      case 'trash':
        if ($exists) {
          $exists->is_delete = 1;
          $exists->update();
        }
        return $this->sendJson('completed', 200);
      case 'failed':
      case 'cancelled':
        if ($exists) {
          $exists->is_cancel = 1;
          $exists->update();
        }
        return $this->sendJson('completed', 200);
      case 'completed':
      case 'processing':
      default:
        if ($is_new) {
          $order->order_status = 1;
          $order->pay_time = new \DateTime();
        }
        break;
    }

    if ($is_new) {
      $order->address = $address ?? $order->address;
      $order->phone = $phone ?? $order->phone;
      $order->realname = $realname ?? $order->realname;
      $order->note = $note ?? $order->note;
      $order->generateOrderSN();
      $order->pay_price = $all_price;
      $order->takeway_order = 'orderdata_' . $id;
      $order->is_takeway = 1;
      // is_checked=0 order needs to be verified by human; is_checked=1 order will get into the kitchen system 
      $order->is_checked = 0;
      $order->desk_id = $desk->id;

      if (isset($metas['is_vat_exempt'])) {
        $order->is_vat_exempt = $metas['is_vat_exempt'] == 'yes' ? 1 : 0;
      }

      if (isset($metas['_before_checkout_billing_form_pick_up_or_delivery'])) {
        $order->is_delivery = $metas['_before_checkout_billing_form_pick_up_or_delivery'] == 'delivery' ? 1 : 0;
      }

      if (isset($metas['_order_date'])) {
        $time = '';
        if ($order->is_delivery == 0 && isset($metas['_order_time']) && !empty($metas['_order_time'])) {
          if ($metas['_order_time'] > 1000) {
            $time1 = mb_strcut($metas['_order_time'], 0, 2);
            $time2 = mb_strcut($metas['_order_time'], 2, 2);

            if ($time2 == '30') {
              if ($time1 >= '12') {
                $next_time1 = 1;
              } else {
                $next_time1 = (intval($time1) + 1);
              }
            } else {
              $next_time1 = $time1;
            }

            $time = ($time1 == 12 ? 'PM ' : 'AM ') . $time1 . ':' . $time2 . ' - ' . ($next_time1 == 1 || $next_time1 == 12 ? 'PM ' : 'AM ') . $next_time1 . ':' . ($time2 == '00' ? '30' : '00');
          } else {
            $time1 = mb_strcut($metas['_order_time'], 0, 1);
            $time2 = mb_strcut($metas['_order_time'], 1, 2);

            if ($time2 == '30') {
              $next_time1 = (intval($time1) + 1);
            } else {
              $next_time1 = $time1;
            }

            $time = 'PM ' . $time1 . ':' . $time2 . ' - PM ' . $next_time1 . ':' . ($time2 == '00' ? ($next_time1 == 9 ? '15' : '30') : '00');
          }
        } else {
          if ($order->is_delivery == 1 && isset($metas['_order_estimated_delivery_time'])) {
            if (!empty($metas['_order_estimated_delivery_time'])) {
              list($time1, $time2) = explode('.', $metas['_order_estimated_delivery_time']);
              $time = 'PM' . $time1 . ':' . $time2 . ' - ' . 'PM ' . (intval($time1) + 1) . ':' . $time2;
            }
          }
        }

        $date = new DateTime();
        $year = date('y');
        $day = date('d');
        $month = date('m');
        if (str_contains($metas['_order_date'], '.')) {
          list($day, $month, $year) = explode('.', $metas['_order_date'], 3);
        }

        $date->setDate($year, $month, $day);
        $order->delivery_order_date = $date->format('Y-m-d     ') . $time;
      }

      $deskOrderCount = Order::where('desk_id', $desk->id)
        ->whereIn('order_status', [0, 1])
        ->where('is_delete', 0)
        ->where('is_cancel', 0)
        ->count();

      if ($deskOrderCount >= 1) {
        $lastCount = Order::where('desk_id', $desk->id)
          ->whereIn('order_status', [0, 1])
          ->where('is_delete', 0)
          ->where('is_cancel', 0)
          ->where('is_pin', 1)
          ->count();

        if ($lastCount >= 1) {
          $last = Order::query()
            ->where('desk_id', $desk_id)
            ->whereIn('order_status', [0, 1])
            ->where('is_delete', 0)
            ->where('is_pin', 1)
            ->where('is_cancel', 0)
            ->orderBy('pin_num', 'desc')
            ->first();
          $deskOrderCount = $last->pin_num + 1;
        }

        $order->is_pin = 1;
        $order->pin_num = $deskOrderCount;
      }
    }

    if ($order->pay_price == 0) {
      return $this->sendJson('', 200);
    }

    if ($is_new) {
      $res = $order->save();
      if ($res) {

        $oid = $order->oid;

        $menuList = [];
        foreach ($menu_order as $data) {
          if (isset($data['product_id']) && !empty($data)) {
            $note = '';
            if (count($data['meta_data']) > 0) {
              $extend = [
                'Large' => '加大',
                'Meat' => '加肉',
                'Vegs' => '加菜',
              ];
              foreach ($data['meta_data'] as $data2) {
                if (is_string($data2['value'])) {
                  $value = explode('&#', html_entity_decode($data2['value'], ENT_HTML5));
                  if (count($value) <= 1) {
                    $value = explode('$', $data2['value']);
                  }
                  $note .= $data2['key'] . ' -> ' . trim($value[0], ' +') . "\n";
                }
              }
            }
            $menuInfo = Menu::where('out_site_id', $data['product_id'])->first();
            if ($menuInfo) {
              $menuList[] = [
                'oid' => $oid,
                'menu_id' => $menuInfo->id,
                'menu_name' => $menuInfo->menu_name,
                'total' => $data['quantity'],
                'total_price' => $data['subtotal'],
                'add_time' => new \DateTime(),
                'note' => $note
              ];
            }
          }
        }

        foreach ($menuList as $data) {
          $orderDetail = new OrderDetail();
          $orderDetail->oid = $data['oid'];
          $orderDetail->menu_id = $data['menu_id'];
          $orderDetail->menu_name = $data['menu_name'];
          $orderDetail->total = $data['total'];
          $orderDetail->total_price = $data['total_price'];
          $orderDetail->add_time = $data['add_time'];
          $orderDetail->note = $data['note'];
          $orderDetail->setPrice();
          $orderDetail->save();
        }

        $message->addMessage('订单通知', '有网站的新订单', musicFile: $this->getOption('takeway_type1_audio'));
        // 将桌位的状态设为已使用
        if ($desk) {
          $desk->use_status = 1;
          $desk->update();
        }

        $this->addJsonData('data', [
          'order_id' => $order->order_sn
        ]);
        return $this->sendJson('创建完成', 200);
      }
    } else {
      return $this->sendJson('更新完成', 200);
    }
    //        file_put_contents(__DIR__ . '/error2', $orderModel->getLastSQL());
    return $this->sendJson('添加失败', 500);
  }
}
