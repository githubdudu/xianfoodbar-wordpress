<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\Schema;
use App\Core\TableButtonLayout;
use App\Core\TableLayout;
use App\Form\MenuDiscountType;
use App\Model\DiscountItem;
use App\Model\Menu;
use App\Model\MenuDiscount;
use App\Service\AdminMenuGenerator;
use Swoole\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuDiscountController extends Wordpress
{
    public function initAdminMenu(AdminMenuGenerator $menu): void
    {
        if ($this->isAdmin()) {
            if (defined('SHOW_DISCOUNT')) {
                $menu->addMenus($this->generateTableArray('menuDiscount', 'list'), '菜品折扣', [
                    'name' => 'UnorderedListOutlined'
                ], useRouter: true);
            }
        }
    }

    public function list(): Response
    {
        $table = new TableLayout('菜品折扣', '菜品折扣');

        $addButton = new TableButtonLayout();
        $addButton
            ->setText('添加菜品折扣')
            ->setLink($this->generateFormUrl('menuDiscount', 'form'))
            ->isRouter();

        $editButton = new TableButtonLayout();
        $editButton
            ->setText('编辑')
            ->setLink($this->generateFormUrl('menuDiscount', 'form'))
            ->setAjaxId('id')
            ->isRouter();

        $deleteButton = new TableButtonLayout();
        $deleteButton
            ->setText('删除')
            ->setAjax($this->generateUrl('api_admin_delete_menu_discount'))
            ->setColor('#be5555')
            ->setAjaxId('id');

        $unactiveButton = new TableButtonLayout();
        $unactiveButton
            ->setText('停用')
            ->setAjax($this->generateUrl('api_admin_unactive_menu_discount'))
            ->setChangeRule('recore.is_delete == 1')
            ->setColor('#e46a6a')
            ->setAjaxId('id');

        $activeButton = new TableButtonLayout();
        $activeButton
            ->setText('启用')
            ->setChangeRule('recore.is_delete == 0 ')
            ->setAjax($this->generateUrl('api_admin_active_menu_discount'))
            ->setColor('#37a037')
            ->setAjaxId('id');

        $table->addCol('id', 'ID');
        $table->addCol('title', '项目名称');
        $table->addCol('description', '项目描述');
        $table->addCol('discount_type', '折扣类型', options: [
            'valueEnum' => [
                0 => '金额',
                1 => '百分比',
            ]
        ]);
        $table->addCol('discount', '折扣', [
            'span',
            [],
            '$Function(recore.discount_type == 0 ? recore.discount_amount : recore.discount_percent + \' % \')$',
        ]);
        $table->addCol('menus', '菜品', options: [
            'search' => false,
            'valueType' => 'code',
        ]);

        $table->addButton($editButton);
        $table->addButton($unactiveButton);
        $table->addButton($activeButton);
        $table->addButton($deleteButton);
        $table->addToolButton($addButton);
        $table->changeApilink('data', $this->router->generate('api_admin_menu_discount_list'));

        return $this->tableTemplate($table);
    }

    public function form(?string $id = '')
    {
        $scheme = new Schema('添加桌位', '显示的桌位');
        $scheme->transform($this->createForm(MenuDiscountType::class));
        $scheme->setPostApiAddress($this->generateUrl('api_admin_menu_discount_create'));
        $scheme->setEditApiAddress($this->generateUrl('api_admin_menu_discount_edit'));
        if ($id > 0) {
            $deskModel = new MenuDiscount();
            $desk = $deskModel->find($id);

            if ($desk) {
                $desk = $desk->toArray();
                $menuIds = $desk['discount_menus'];
                $desk['discount_type'] = strval($desk['discount_type']);
                $ids = [];
                $mnus = Menu::whereIn('id', $menuIds)->get();
                foreach ($mnus as $mnu) {
                    $ids[] = intval($mnu->menu_num);
                }
                $desk['meuns'] = $ids;
                $desk['time_range'] = [$desk['discount_start_time'], $desk['discount_end_time']];

                $scheme->setFormData($desk);
                $scheme->setNowType('edit');
            }
        }

        return $this->formTamplate($scheme);
    }

    #[Route('/api/admin/menu/discount/list', name: 'api_admin_menu_discount_list')]
    public function getList(): Response
    {
        if ($this->isLogin()) {
            // current=1&pageSize=20&desk_status=1
            $paged = $this->request->query->get('current', 1);
            $pageSize = $this->request->query->get('pageSize', 30);
            $deskStatus = $this->request->query->get('desk_status', null);
            $id = $this->request->query->get('id', null);
            $desk_name = $this->request->query->get('desk_name', null);

            $where = [];

            if ($deskStatus !== null) {
                $where['desk_status'] = $deskStatus;
            }

            if ($id !== null && $id > 0) {
                $where['id'] = $id;
            }

            if ($desk_name !== null) {
                $where[] = ['desk_name', 'like', "%{$desk_name}%"];
            }

            $desk = new MenuDiscount();
            $desk->with('items');
            if (count($where) > 0) {
                $desk->where($where);
            }
            $total = $desk->query()->count();
            $page = $desk->orderBy('id', 'desc')->offset($pageSize * ($paged - 1))->limit($pageSize)->get();

            foreach ($page as $key => $value) {
                //  $value->menus = implode(' , ', $value->menus);
                $value->key = $key;
                if ($value->discount_menus) {
                    $menus = Menu::whereIn('id', $value->discount_menus)->get();
                    $menuString = [];
                    $tempMenu = [];

                    foreach ($menus as $menu) {
                        $tempMenu[] = $menu->menu_name;
                        if (count($tempMenu) >= 3) {
                            $menuString[] = implode(' , ', $tempMenu);
                            $tempMenu = [];
                        }
                    }
                    $value->menus = implode(PHP_EOL, $menuString);
                }
            }

            if ($page) {
                $this->addJsonData('data', $page);
                $this->addJsonData('total', $total);
                return $this->sendJson();
            }

            return $this->sendJson('未找到', 404);
        }

        return $this->sendJson([
            'data' => [],
            'total' => 0,
        ]);
    }

    #[Route('/api/admin/menu_discoun/create', name: 'api_admin_menu_discount_create')]
    #[Route('/api/admin/menu_discoun/edit/{id}', name: 'api_admin_menu_discount_edit')]
    public function addMenuDiscount(int $id = 0): Response
    {
        if ($this->isLogin()) {
            $title = $this->request->get('title', '');
            $description = $this->request->get('description', '');
            $discount_type = $this->request->get('discount_type', 0);
            $discount_amount = $this->request->get('discount_amount', 0);
            $discount_percent = $this->request->get('discount_percent', 0);
            $time_range = $this->request->get('time_range', null);
            $menus = $this->request->get('meuns', []);

            if (!$title) {
                return $this->sendJson('标题不能为空', 500);
            }

            if (!is_numeric($discount_type)) {
                return $this->sendJson('折扣类型不能为空', 500);
            }

            $discount = null;
            if ($id > 0) {
                $discount = MenuDiscount::find($id);
            }

            $discount = $discount ?? new MenuDiscount();
            $discount->title = $title;
            $discount->description = $description;
            $discount->discount_type = $discount_type;
            $discount->discount_amount = $discount_amount;
            $discount->discount_percent = $discount_percent;

            if (is_array($time_range)) {
                $discount->discount_start_time = $time_range[0];
                $discount->discount_end_time = $time_range[1];
            } else {
                return $this->sendJson('时间范围格式错误', 500);
            }

            $menuId = [];
            foreach ($menus as $menu) {
                $menuData = Menu::where('menu_num', $menu)->first();
                if ($menuData) {
                    $menuId[] = $menuData->id;
                }
            }

            $discount->discount_menus = $menuId;

            $result = $id > 0 ? $discount->update() : $discount->save();
            if ($result) {
                return $this->sendJson($id > 0 ? '修改成功' : '添加成功', 200);
            }
            return $this->sendJson($id > 0 ? '修改失败' : '添加失败', 500);
        }
        return $this->sendJson('没有权限访问', 403);
    }

    #[Route('/api/admin/menu_discoun/active/{id}', name: 'api_admin_active_menu_discount')]
    #[Route('/api/admin/menu_discoun/unactive/{id}', name: 'api_admin_unactive_menu_discount')]
    public function active(int $id = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin()) {
            $menuDiscount = MenuDiscount::find($id);
            if ($menuDiscount) {
                $menuDiscount->is_delete = $menuDiscount->is_delete > 0 ? 0 : 1;
                if ($menuDiscount->update()) {
                    return $this->sendJson($menuDiscount->is_delete == 0 ? '启用成功' : '停用成功', 200);
                }
                return $this->sendJson($menuDiscount->is_delete == 0 ? '启用失败' : '停用失败', 500);
            }
            return $this->sendJson('项目不存在', 404);
        }
        return $this->sendJson('没有权限访问', 403);
    }

    #[Route('/api/admin/menu_discoun/delete/{id}', name: 'api_admin_delete_menu_discount')]
    public function delete(int $id = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin()) {
            $menuDiscount = MenuDiscount::find($id);
            if ($menuDiscount) {
                if ($menuDiscount->delete()) {
                    return $this->sendJson('删除成功', 200);
                }
                // fail
                return $this->sendJson('删除失败', 500);
            }
            return $this->sendJson('项目不存在', 404);
        }
        return $this->sendJson('没有权限访问', 403);
    }
}
