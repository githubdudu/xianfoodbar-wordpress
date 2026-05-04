<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\CheckType;
use App\Core\TableButtonLayout;
use App\Core\TableLayout;
use App\Model\Desk;
use App\Service\AdminMenuGenerator;
use Symfony\Component\Routing\Annotation\Route;

class DeskController extends Wordpress
{
  public function initAdminMenu(AdminMenuGenerator $menu): void
  {
    if ($this->isAdmin()) {
      $menu->addMenus($this->generateTableArray('desk', 'list'), '桌位列表', [
        'name' => 'UnorderedListOutlined'
      ], useRouter: true);
    }
  }

  #[Route('/admin/desk/change/status/{id}', name: 'admin_desk_change_status')]
  public function activeDesk(int $id = 0): \Symfony\Component\HttpFoundation\Response
  {
    if ($id && $this->isAdmin()) {
      $desk = Desk::find($id);

      if ($desk) {
        $desk->desk_status = $desk->desk_status == 0 ? 1 : 0;
        $result = $desk->update();

        if ($result) {
          return $this->sendJson('操作成功');
        }
      }

      return $this->sendJson('操作失败', 500);
    }

    return $this->sendJson('非法访问', 403);
  }

  #[Route('/admin/desk/unactive/{id}', name: 'admin_desk_unactive')]
  public function unactiveDesk(int $id = 0)
  {
    if ($id && $this->isAdmin()) {
      $desk = Desk::find($id);

      if ($desk) {
        $desk->desk_status = 0;
        $result = $desk->update();

        if ($result) {
          return $this->sendJson('操作成功');
        }
      }

      return $this->sendJson('操作失败', 500);
    }

    return $this->sendJson('非法访问', 403);
  }

  #[Route('/api/admin/desk/clean/status/{id}', name: 'admin_desk_clean_status')]
  public function cleanStatus($id = 0)
  {
    if ($this->isLogin()) {
      if ($id > 0) {
        $desk = Desk::find($id);
        if ($desk) {
          $desk->use_status = 0;
          if ($desk->update()) {
            return $this->sendJson('操作成功');
          }
          return $this->sendJson('操作失败', 500);
        }
      }
      return $this->sendJson('错误的桌位', 404);
    }

    return $this->sendJson('非法访问', 403);
  }

  public function list(): \Symfony\Component\HttpFoundation\Response
  {
    $table = new TableLayout('桌位列表', '桌位的管理列表');

    $editButton = new TableButtonLayout();
    $editButton
      ->setText('编辑桌位')
      ->setLink($this->generateFormUrl('deskForm', 'list'))
      ->setAjaxId('id')
      ->isRouter();

    $addDeskButton = new TableButtonLayout();
    $addDeskButton
      ->setLink($this->generateFormUrl('deskForm', 'list'))
      ->isRouter()
      ->setText('添加桌位');

    $cleanStatusButton = new TableButtonLayout();
    $cleanStatusButton
      ->setText('清除状态')
      ->setAjax($this->generateUrl('admin_desk_clean_status'))
      ->setAjaxId('id')
      ->setColor('#a3501c');

    $delDeskButton = new TableButtonLayout();
    $delDeskButton
      ->setAjax($this->generateUrl('api_admin_delete_desk'))
      ->setAjaxId('id')
      ->setColor('#ff4d4f')
      ->setText('删除桌位');

    $domain = $this->request->getUriForPath('');

    $table
      ->addCol('id', 'ID')
      ->addCol('desk_name', '桌号名称')
      ->addCol('add_time', '创建时间', options: [
        'search' => false,
      ])
      ->addCol('qr_url', 'QRCode', [
        'Qrcode',
        [
          'value' => '$text$'
        ]
      ], [
        'search' => false,
      ])
      ->addCol(
        'desk_status',
        '状态',
        [
          'Button',
          [
            'size' => 'small',
            'type' => 'primary',
            'style' => [
              'background' => '$Function(recore.desk_status == 1 ?  "#37a037" : "#e46a6a")$',
              'border' => 'none',
            ],
            'onClick' => [
              'id' => 'id',
              'url' => $this->router->generate('admin_desk_change_status'),
            ]
          ],
          '$text$'
        ],
        options: [
          'valueEnum' => [
            0 => '未启用',
            1 => '已启用'
          ]
        ]
      )
      ->addButton($editButton)
      ->addButton($delDeskButton)
      ->addButton($cleanStatusButton)
      ->addToolButton($addDeskButton)
      ->changeApilink('data', $this->router->generate('admin_desk_list'));

    return $this->tableTemplate($table);
  }

  #[Route('/api/desk/list', name: 'admin_desk_list')]
  public function getList(): \Symfony\Component\HttpFoundation\Response
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

      $desk = new Desk();
      $page = $desk->where($where)->offset(($paged - 1) * $pageSize)->limit($pageSize);
      $all = $page->get();
      $total = Desk::count();

      if ($all) {
        $this->addJsonData('data', $all);
        $this->addJsonData('total', $total);
        return $this->sendJson();
      }

      return $this->sendJson('未找到', 404);
    }
    return $this->sendJson('没有权限', 403);
  }

  #[Route('/api/admin/desk/all_desk/{type}', name: 'api_admin_get_all_desk')]
  public function allDesk(): \Symfony\Component\HttpFoundation\Response
  {
    $data = [];

    if ($this->isLogin()) {
      $data = Desk::where('id', '>', 0)->get();

      $this->addJsonData('data', $data);
      return $this->sendJson();
    }
    return $this->sendJson('', 403);
  }

  #[Route('/api/admin/remove/desk/{id}', name: 'api_admin_delete_desk')]
  public function removeDesk(int $id = 0): \Symfony\Component\HttpFoundation\Response
  {
    if ($this->isAdmin()) {
      $desk = Desk::find($id);

      if ($desk) {
        $res = $desk->delete();

        if ($res) {
          return $this->sendJson('删除成功');
        }
      }
    }

    return $this->sendJson('', 403);
  }
}
