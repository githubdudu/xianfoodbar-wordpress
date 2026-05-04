<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\Schema;
use App\Form\MenuType;
use App\Model\Menu;
use Symfony\Component\Routing\Annotation\Route;

class MenuFormController extends Wordpress
{
    public function list(?string $id = null)
    {
        $schema = new Schema('添加菜品', '添加菜品');
        if (!empty($id)) {
            $schema = new Schema('修改菜品', '修改菜品');
            $schema->setNowType('edit');
            $menu = Menu::find($id);

            if ($menu) {
                // $menu->setMenuPrice($menu->getMenuPrice() . '');
                $schema->setFormData($menu);
            }
        }

        $schema->transform($this->createForm(MenuType::class));
        $schema->setPostApiAddress($this->generateUrl('api_add_menu_admin'));
        $schema->setEditApiAddress($this->generateUrl('api_edit_menu_admin'));

        return $this->formTamplate($schema);
    }

    #[Route('/api/admin/menu/add', name: 'api_add_menu_admin')]
    public function addMenu()
    {
        if ($this->isAdmin()) {

            $menu_name = $this->request->request->get('menu_name', '');
            $menu_count = $this->request->request->get('menu_count', 0);
            $menu_num = $this->request->request->get('menu_num', 0);
            $menu_price = $this->request->request->get('menu_price', '0.00');
            $menu_sales = $this->request->request->get('menu_sales', 0);
            $out_site_id = $this->request->request->get('out_site_id', 0);
            $menu_category = $this->request->request->get('menu_category', 0);
            $menu_subname = $this->request->request->get('menu_subname', '');
            $menu_note = $this->request->request->get('menu_note', '');

            $menu = new Menu();

            $menu->add_time = new \DateTime();
            $menu->menu_name = $menu_name;
            $menu->menu_count = (int) $menu_count;
            $menu->menu_price = $menu_price;
            $menu->menu_sales = $menu_sales;
            $menu->out_site_id = $out_site_id;
            $menu->menu_num = $menu_num;
            $menu->menu_subname = $menu_subname;
            $menu->menu_note = $menu_note;
            $menu->menu_category = $menu_category;

            $res = $menu->save();

            if ($res) {
                return $this->sendJson('添加成功', 200);
            }

            return $this->sendJson('添加失败', 500);
        }

        return $this->sendJson('未知权限', 500);
    }

    #[Route('/api/admin/menu/edit/{id}', name: 'api_edit_menu_admin')]
    public function editMenu(int $id = 0)
    {
        if ($id > 0 && $this->isAdmin()) {
            $menu_name = $this->request->request->get('menu_name', '');
            $menu_count = $this->request->request->get('menu_count', 0);
            $menu_num = $this->request->request->get('menu_num', 0);
            $menu_price = $this->request->request->get('menu_price', '0.00');
            $menu_sales = $this->request->request->get('menu_sales', 0);
            $out_site_id = $this->request->request->get('out_site_id', 0);
            $menu_category = $this->request->request->get('menu_category', 0);
            $menu_subname = $this->request->request->get('menu_subname', '');
            $menu_note = $this->request->request->get('menu_note', '');

            $menu = Menu::find($id);

            if ($menu) {
                $menu->menu_name = $menu_name;
                $menu->menu_count = (int) $menu_count;
                $menu->menu_price = $menu_price;
                $menu->menu_sales = $menu_sales;
                $menu->out_site_id = $out_site_id;
                $menu->menu_num = $menu_num;
                $menu->menu_subname = $menu_subname;
                $menu->menu_note = $menu_note;
                $menu->menu_category = $menu_category;

                $menu->update();
                return $this->sendJson('修改成功', 200);
            }

            return $this->sendJson('发生错误', 500);
        }

        return $this->sendJson('未知权限', 500);
    }
}
