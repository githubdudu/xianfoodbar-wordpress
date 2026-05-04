<?php

namespace App\Controller;

use App\Core\Controller\Wordpress;
use App\Model\Desk;
use Symfony\Component\Routing\Annotation\Route;

class DeskController extends Wordpress
{
    #[Route("/api/desk/get/{desk_id}", name:"get_desk_info2")]
    public function getDesk($desk_id = 0)
    {
        $canQrcode = $this->getOption('can_qrcode', true);
        if (!$canQrcode) {
            $this->addJsonData('is_always', true);
            return $this->sendJson('请前往前台点餐<br />Please go to the front to order'
                , 500);
        }

        if ($desk_id > 0) {
            $desk  = Desk::find($desk_id);

            if ($desk) {
                $this->addJsonData('data', $desk);
                return $this->sendJson();
            }
        }
        return $this->sendJson('无法访问', 404);
    }

}
