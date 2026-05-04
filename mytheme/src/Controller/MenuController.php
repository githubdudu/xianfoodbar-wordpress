<?php

namespace App\Controller;

use App\Core\Controller\Wordpress;
use App\Model\Menu;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends Wordpress
{
    #[Route('/api/menu/get/{id}', name: 'index_get_menu_data')]
    public function getOne($id = 0)
    {
        $canQrcode = $this->getOption('can_qrcode', true);
        if (!$canQrcode) {
            $this->addJsonData('is_always', true);
            return $this->sendJson('请前往前台点餐<br />Please go to the front to order',
                500);
        }

        $data = Menu::where('menu_num', $id)->first();

        if ($data) {
            if (defined('SHOW_DISCOUNT')) {
                $data->menu_price = (new \App\Model\MenuDiscount())->getDiscountPrice($data);
            }
            $data = $data->toArray();
            $data['menu_price'] = number_format(floatval($data['menu_price']), 2, '.', '');
            $this->addJsonData('data', $data);
            return $this->sendJson();
        }

        return $this->sendJson('未找到数据', 404);
    }
}
