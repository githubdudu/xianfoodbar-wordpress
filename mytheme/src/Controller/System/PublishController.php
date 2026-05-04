<?php

namespace App\Controller\System;

use App\Core\Controller\SSECore as ControllerSSECore;
use App\Service\AdminMessage;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class PublishController
{

  #[Route('/api/admin/message-notifications', name: 'test_sse_route')]
  public function message(AdminMessage $message): Response
  {
    // set_time_limit(100);
    $start = true;
    return ControllerSSECore::createResponse(function () use ($message, &$start) {
      $news = $message->getMessage();
      
      if ($start) {
      	echo " ";
      }

      if (!empty($news)) {
      	$start = false;
        return $news;
      }
    }, 'message', 2, 100, 5);
  }

  #[Route('/test', name: 'test')]
  public function test() {}
}
