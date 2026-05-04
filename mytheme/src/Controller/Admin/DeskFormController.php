<?php

namespace App\Controller\Admin;

use App\Core\CheckType;
use App\Core\Controller\Wordpress;
use App\Core\Schema;
use App\Form\DeskType;
use App\Model\Desk;
use Symfony\Component\Routing\Annotation\Route;

class DeskFormController extends Wordpress
{
    public function list(?string $id = '')
    {
        $scheme = new Schema('添加桌位', '显示的桌位');
        $scheme->transform($this->createForm(DeskType::class));
        $scheme->setPostApiAddress($this->generateUrl('api_admin_add_desk'));
        $scheme->setEditApiAddress($this->generateUrl('api_admin_edit_desk'));
        if ($id > 0) {
            $deskModel = new Desk();
            $desk = $deskModel->find($id);


            if ($desk) {
                $scheme->setFormData($desk);
                $scheme->setNowType('edit');
            }
        }

        return $this->formTamplate($scheme);
    }

    #[Route('/api/admin/desk/add', name: 'api_admin_add_desk')]
    public function add(): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->request->isMethod('POST')) {
            if (!$this->isAdmin()) {
                return $this->sendJson('没有权限', 403);
            }

            $desk_name = $this->request->request->get('desk_name');
            $desk_subname = $this->request->request->get('desk_subname');
            $menu_guid = $this->request->request->get('menu_guid');
            $is_takeway = $this->request->request->get('is_takeway', 0);

            if (empty($desk_name)) {
                return $this->sendJson('桌号名不能为空', 500);
            }
            $desk = new Desk();
            $desk->desk_name = $desk_name;
            $desk->menu_guid = $menu_guid;
            $desk->desk_subname = $desk_subname;
            $desk->is_takeway = $is_takeway;

            $res = $desk->save();

            if ($res) {
                return $this->sendJson('添加成功', 200);
            }

            return $this->sendJson('添加失败', 500);
        }

        return $this->sendJson('无法找到接口', 404);
    }

    #[Route('/api/admin/desk/edit/{id}', name: 'api_admin_edit_desk')]
    public function edit(string $id = '')
    {
        if ($this->request->isMethod('PUT')) {
            if (!$this->isAdmin()) {
                return $this->sendJson('没有权限', 403);
            }

            $desk_name = $this->request->request->get('desk_name');
            $desk_subname = $this->request->request->get('desk_subname');
            $menu_guid = $this->request->request->get('menu_guid');
            $is_takeway = $this->request->request->get('is_takeway', 0);

            if (empty($desk_name)) {
                return $this->sendJson('桌号名不能为空', 500);
            }

            $deskModel = new Desk();
            $desk = $deskModel->find($id);
            if (!$desk) {
                return $this->sendJson('找不到桌位', 500);
            }
            $desk->desk_name = $desk_name;
            $desk->menu_guid = $menu_guid;
            $desk->desk_subname = $desk_subname;
            $desk->is_takeway = $is_takeway;

            $res = $desk->update();

            if ($res) {
                return $this->sendJson('修改成功', 200);
            }

            return $this->sendJson('修改成功', 500);
        }

        return $this->sendJson('无法找到接口', 404);
    }
}
