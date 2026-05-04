<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\TableButtonLayout;
use App\Core\TableLayout;
use App\Model\Menu;
use App\Model\MenuCategory;
use App\Service\AdminMenuGenerator;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends Wordpress
{
  public function initAdminMenu(AdminMenuGenerator $menu)
  {
    if ($this->isAdmin()) {
      $menu->addMenus($this->generateTableArray('menu', 'list'), '菜品列表', [
        'name' => 'UnorderedListOutlined'
      ], useRouter: true);
    }
  }

  #[Route('/adminpanel/page/Menu/List/test1', name: 'test_pages_list2')]
  public function test(): \Symfony\Component\HttpFoundation\Response
  {
    return $this->sendJson();
  }

  public function list(): \Symfony\Component\HttpFoundation\Response
  {
    $tempMenu = [];
    $menuCate = MenuCategory::all();

    foreach ($menuCate as $menu) {
      $tempMenu[$menu->id] = $menu->category_name;
    }

    $table = new TableLayout('菜单管理', '菜单的管理列表');

    $addButtonLayout = new TableButtonLayout();
    $addButtonLayout
      ->setText('添加菜单')
      ->setLink($this->generateFormUrl('menuForm', 'list'))
      ->isRouter();

    $editButtonLayout = new TableButtonLayout();
    $editButtonLayout
      ->setText('修改菜单')
      ->setAjaxId('id')
      ->setLink($this->generateFormUrl('menuForm', 'list'))
      ->isRouter();

    $delButtonLayout = new TableButtonLayout();
    $delButtonLayout
      ->setText('删除菜单')
      ->setAjaxId('id')
      ->setAjax($this->generateUrl('api_admin_menu_delete'))
      ->setColor('#d64a4a');

    $table
      ->addCol('id', 'ID', options: [
        'editable' => false,
      ])
      ->addCol('menu_name', '菜品名称', options: [
        'editable' => false,
        'editableKeys' => ['menu_name']
      ])
      ->addCol('menu_num', '菜品编号')
      ->addCol('menu_category', '菜品分类', options: [
        'valueEnum' => $tempMenu,
        'input'
      ])
      ->addCol('menu_price', '价格', options: [
        'search' => false,
      ])
      ->addCol('menu_sales', '销量', options: [
        'search' => false,
      ])
      ->addCol(
        'is_delete',
        '状态',
        [
          'Button',
          [
            'size' => 'small',
            'type' => 'primary',
            'style' => [
              'background' => '$Function(recore.is_delete == 1 ? "#cea646" : "#4a9657")$',
              'border' => 'none',
            ],
            'onClick' => [
              'url' => '$Function(recore.is_delete === 1 ? "/api/admin/menu/active" : "/api/admin/menu/active/2")$',
              'id' => 'id',
            ]
          ],
          '$text$'
        ],
        options: [
          'valueEnum' => [
            0 => '已上架',
            1 => '已下架'
          ]
        ]
      )
      ->addCol('add_time', '创建时间', options: [
        'search' => false,
      ])
      // ->addButton($avtiveButton)
      ->addButton($editButtonLayout)
      ->addButton($delButtonLayout)
      ->addToolButton($addButtonLayout)
      ->changeApilink('data', $this->router->generate('api_admin_menu_list'));

    return $this->tableTemplate($table);
  }

  #[Route('/api/admin/menu/list', name: 'api_admin_menu_list')]
  public function getList(): \Symfony\Component\HttpFoundation\Response
  {
    if ($this->isLogin()) {
      // current=1&pageSize=20&desk_status=1
      $paged = $this->request->query->get('current', 1);
      $pageSize = $this->request->query->get('pageSize', 30);
      $menu_name = $this->request->query->get('menu_name', null);
      $did = $this->request->query->get('id', null);
      $menu_num = $this->request->query->get('menu_num', null);
      $is_delete = $this->request->query->get('is_delete', null);
      $menu_category = $this->request->query->get('menu_category', null);

      $where = [];

      if ($is_delete !== null) {
        $where['is_delete'] = $is_delete;
      }

      if ($menu_category !== null) {
        $where['menu_category'] = $menu_category;
      }

      if ($did !== null && $did > 0) {
        $where['id'] = $did;
      }

      if ($menu_num !== null) {
        $where[] = ['menu_num', 'like', "%{$menu_num}%"];
      }

      if ($menu_name !== null) {
        $where[] = ['menu_name', 'like', "%{$menu_name}%"];
      }

      $desk = new Menu();
      $page = $desk->where($where)->offset(($paged - 1) * $pageSize)->limit($pageSize);
      $all = $page->get();
      $total = Menu::count();

      if ($all) {
        $this->addJsonData('data', $all);
        $this->addJsonData('total', $total);
        return $this->sendJson();
      }

      return $this->sendJson('未找到', 404);
    }
    return $this->sendJson('没有权限', 403);
  }

  #[Route('/api/admin/menu/active/{type}/{id}', name: 'api_admin_menu_active_unactive', defaults: ['type' => 0, 'id' => 0])]
  public function active(int $type = 0, int $id = 0): \Symfony\Component\HttpFoundation\Response
  {
    if ($this->isAdmin() && ($id != 0 || $type != 0)) {
      if ($id == 0 && $type > 0) {
        $id = $type;
        $type = 0;
      }

      $menu = Menu::find($id);
      $menu->is_delete = ($type == 2 ? 1 : 0);

      $res = $menu->update();
      if ($res) {
        return $this->sendJson('修改成功', 200);
      }
      return $this->sendJson('发生错误', 500);
    }

    return $this->sendJson('没有权限', 403);
  }

  #[Route('/api/admin/menu/delete/{id}', name: 'api_admin_menu_delete')]
  public function delete(int $id = 0): \Symfony\Component\HttpFoundation\Response
  {
    if ($this->isAdmin()) {
      $menu = Menu::find($id);

      if ($menu) {
        $menu->delete();
        return $this->sendJson('操作成功');
      }

      return $this->sendJson('not found', 404);
    }

    return $this->sendJson('权限不够', 403);
  }
}
