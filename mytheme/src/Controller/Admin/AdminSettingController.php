<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\Schema;
use App\Form\SettingType;
use Symfony\Component\Routing\Annotation\Route;

class AdminSettingController extends Wordpress
{
    public function list()
    {
        $scheme = new Schema('系统设置', '系统设置');
        $scheme->setPostApiAddress($this->generateUrl('api_admin_save_setting'))->setFormData([
            'desk_audio' => $this->getOption('desk_audio') ?? '',
            'takeway_type1_audio' => $this->getOption('takeway_type1_audio', ''),
            'takeway_type2_audio' => $this->getOption('takeway_type2_audio', ''),
            'takeway_type3_audio' => $this->getOption('takeway_type3_audio', ''),
            'site_takeway_did' => intval($this->getOption('site_takeway_did', 0)),
            'active_order' => $this->getOption('active_order', '#000'),
            'new_active_order' => $this->getOption('new_active_order', '#000'),
            'big_fonts' => intval($this->getOption('big_fonts', 34)),
            'add_active_order' => $this->getOption('add_active_order', '#000'),
            'can_qrcode' => $this->getOption('can_qrcode', true),
            'cook_intval' => $this->getOption('cook_intval', 5),
        ]);
        $scheme->transform($this->createForm(SettingType::class));
        return $this->formTamplate($scheme);
    }

    #[Route('/api/admin/save/settings', name: 'api_admin_save_setting')]
    public function saveSetting()
    {
        if ($this->isAdmin()) {
            $desk_audio = $this->request->request->get('desk_audio', '');
            $takeway_type1_audio = $this->request->request->get('takeway_type1_audio', '');
            $takeway_type2_audio = $this->request->request->get('takeway_type2_audio', '');
            $takeway_type3_audio = $this->request->request->get('takeway_type3_audio', '');
            $site_takeway_did = $this->request->request->get('site_takeway_did', '');
            $active_order = $this->request->request->get('active_order', '#000');
            $new_active_order = $this->request->request->get('new_active_order', '#000');
            $add_active_order = $this->request->request->get('add_active_order', '#000');
            $big_fonts = $this->request->request->get('big_fonts', 34);
            $can_qrcode = $this->request->request->get('can_qrcode', false);
            $cook_intval = $this->request->request->get('cook_intval', 5);

            $this->setOption('desk_audio', $desk_audio);
            $this->setOption('takeway_type1_audio', $takeway_type1_audio);
            $this->setOption('takeway_type2_audio', $takeway_type2_audio);
            $this->setOption('takeway_type3_audio', $takeway_type3_audio);
            $this->setOption('site_takeway_did', $site_takeway_did);
            $this->setOption('active_order', $active_order);
            $this->setOption('new_active_order', $new_active_order);
            $this->setOption('big_fonts', $big_fonts);
            $this->setOption('add_active_order', $add_active_order);
            $this->setOption('can_qrcode', $can_qrcode);
            $this->setOption('cook_intval', $cook_intval);

            return $this->sendJson('修改成功', 200);
        }

        return $this->sendJson('', 403);
    }
}
