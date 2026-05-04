<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Entity\Desk;
use App\Model\DeskModel;
use App\Model\ResMenuModel;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends Wordpress {

    #[Route('/api/tests', name: 'entity_tests')]
    public function testRoute(DeskModel $menu) {
        $this->addJsonData('desk', $menu->findAll());
        return $this->sendJson();
    }
}