<?php

namespace App\Controller;

use App\Core\Controller\Wordpress;
use App\Service\RemoteOrderService;
use App\Service\AdminMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

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

    $desk_id = $this->getOption('site_takeway_did', 0);

    $result = $remoteOS->orderSync($orderData, $desk_id);

    switch ($result) {
      case 'skipped-stale':
      case 'skipped-no-total-cost':
        $response = $this->sendJson('', 200);
        break;
      case 'completed-existed':
      case 'completed-trash':
      case 'completed-failed-cancelled':
        $response = $this->sendJson('completed', 200);
        break;
      case '更新完成':
        $response = $this->sendJson('更新完成', 200);
        break;
      case '添加失败':
        $response = $this->sendJson('添加失败', 500);
        break;
      case '创建完成':
        $message->addMessage(
          '订单通知',
          '有网站的新订单',
          musicFile: $this->getOption('takeway_type1_audio')
        );

        // Update desk status
        if ($desk = $remoteOS->getDesk()) {
          $desk->use_status = 1;
          $desk->update();
        }

        // Add order_sn/order_id to response data
        $this->addJsonData('data', [
          'order_id' => $remoteOS->getOrderSn()
        ]);

        $response = $this->sendJson('创建完成', 200);
        break;
      default:
        $response = $this->sendJson('unknown status', 200);
    }
    return $response;
  }
}
