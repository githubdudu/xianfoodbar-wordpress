<?php

namespace App\Controller;

use App\Core\Controller\Wordpress;
use App\Service\RemoteOrderService;
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
  public function getData(AdminMessage $message, RemoteOrderService $remoteOS, ?int $id = null)
  {
    // Request validation
    // Validate the query parameter
    if (!$id) {
      return $this->sendJson("", 404);
    }

    // Validate the request body
    $orderData = $this->request->request->all();
    if (empty($orderData)) {
      return $this->sendJson("", 404);
    }
    // Save the request body to a file
    $remoteOS->saveOrderDataToFile($orderData);

    $menu_order = $orderData['items'];


    $is_new = true;
    $desk_id = $this->getOption('site_takeway_did', 0);

    $result = $remoteOS->orderSync($orderData, $desk_id);

    $response = match ($result) {
      'skipped' => $this->sendJson('', 200),
      'completed' => $this->sendJson('completed', 200),
      '更新完成' => $this->sendJson('更新完成', 200),
      '添加失败' => $this->sendJson('添加失败', 500),
      '创建完成' => $this->sendJson('创建完成', 200),
      default => $this->sendJson('', 200),
    };

    $order = new Order();

    $desk = Desk::find($desk_id);


    if ($is_new) {
      $res = $order->save();
      if ($res) {

        $oid = $order->oid;

        $menuList = [];
        foreach ($menu_order as $data) {
          if (isset($data['product_id']) && !empty($data)) {
            $note = '';
            if (count($data['meta_data']) > 0) {

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
    }
  }
}
