<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\ExcelExport;
use App\Core\ExcelSheet;
use App\Core\TableButtonLayout;
use App\Core\TableLayout;
use App\Model\Desk;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminMenuGenerator;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;

class OrderListController extends Wordpress
{
    public function initAdminMenu(AdminMenuGenerator $menu)
    {
        if ($this->isLogin()) {
            // $menu->addMenus($this->generateTableArray('orderList', 'list'), "订单列表");
            $menu->addMenus($this->generateTableArray('orderList', 'list'), '订单管理', [
                'name' => 'UnorderedListOutlined',
            ], useRouter: true);
        }
    }

    // #[Route('/adminpanel/page/Order/List/test1/tes2', name: 'test_pages_list')]
    // public function test()
    // {
    //    return $this->sendJson();
    // }

    #[Route('/api/admin/order/clean/{type}', name: 'api_admin_order_clean')]
    public function cleanOldOrder(string $type = 'cancel'): mixed
    {
        if ($this->isAdmin()) {
            if ($type == 'cancel') {
                $date = (new DateTime)
                    ->modify('-1 year')
                    ->format('Y-m-d H:i:s');
                Order::where([
                    'order_status' => 0,
                ])->where('create_time', '<=', $date)->delete();
                echo 'delete ok';
                exit;
            }

            if ($type == 'all') {
                $date = (new DateTime)->modify('-1 year')->format('Y-m-d H:i:s');
                Order::where('create_time', '<=', $date)->delete();
                OrderDetail::where('add_time', '<=', $date)->delete();

                echo 'delete ok';
                exit;
            }
            echo 'delete fail';
            exit;

            return $this->sendJson('出错', 500);
        }
        echo '未登录';
        exit;
        // return $this->sendJson('未登录', 401);
    }

    public function list(): mixed
    {
        $table = new TableLayout('订单管理', '订单的管理列表');

        $excelButton = new TableButtonLayout;
        $excelButton
            ->setText('导出Excel')
            ->setLink($this->router->generate('api_admin_order_export_excel'));

        $viewButton = new TableButtonLayout;
        $viewButton
            ->setLink($this->router->generate('admin_order_info'))
            ->setAjaxId('oid')
            ->isRouter()
            ->setText('查看');

        $avtiveButton = new TableButtonLayout;
        $avtiveButton
            ->setAjax($this->router->generate('api_admin_order_change_status'))
            ->setAjaxId('oid')
            ->setChangeRule('recore.order_status == 0')
            ->setAjaxInclude([
                'status' => 0,
            ])
            ->setText('未支付')
            ->setColor('#e46a6a');

        $cleanButton2 = new TableButtonLayout;
        $cleanButton2
            ->setLink($this->router->generate('api_admin_order_clean', ['type' => 'cancel']))
            ->setText('清理未完成订单')
            ->setColor('#e46a6a');

        $cleanButton = new TableButtonLayout;
        $cleanButton
            ->setLink($this->router->generate('api_admin_order_clean', ['type' => 'all']))
            ->setText('清理一年前订单')
            ->setColor('#e46a6a');

        $unactiveButton = new TableButtonLayout;
        $unactiveButton
            ->setAjax($this->router->generate('api_admin_order_change_status'))
            ->setAjaxId('oid')
            ->setChangeRule('recore.order_status == 1')
            ->setText('已支付')
            ->setAjaxInclude([
                'status' => 1,
            ])
            ->setColor('#49a2f1');

        $unactiveButton2 = new TableButtonLayout;
        $unactiveButton2
            ->setAjax($this->router->generate('api_admin_order_change_status'))
            ->setAjaxId('oid')
            ->setChangeRule('recore.order_status == 2')
            ->setText('已完成')
            ->setAjaxInclude([
                'status' => 2,
            ])
            ->setColor('#37a037');

        $cancelButton = new TableButtonLayout;
        $cancelButton
            ->setAjax($this->router->generate('api_admin_cancel_order2'))
            ->setAjaxId('oid')
            ->setChangeRule('recore.is_cancel == 1')
            ->setText('取消订单')
            ->setColor('#be5555');

        $delButton = new TableButtonLayout;
        $delButton
            ->setAjax($this->router->generate('api_admin_order_del'))
            ->setAjaxId('oid')
            ->setText('彻底删除')
            ->setColor('#be5555');

        $recoverButton = new TableButtonLayout;
        $recoverButton
            ->setAjax($this->router->generate('api_admin_order_del_recover'))
            ->setAjaxId('oid')
            ->setChangeRule('recore.is_cancel == 0')
            ->setText('恢复订单')
            ->setColor('#37a037');

        $table
            ->addCol('oid', 'ID')
            ->addCol('order_sn', '订单编号', options: [
                'editable' => true,
                'editableKeys' => ['menu_name'],
                'copyable' => true,
            ])
            ->addCol('desk', '桌号', [
                'span',
                [],
                '$Function((text ? recore.desk_name : "无") + "(搜索ID: " + recore.desk_id + ")")$',
            ])
            ->addCol(
                'pay_price',
                '消费金额',
                [
                    'span',
                    [],
                    '$Function(parseFloat(recore.pay_price).toFixed(2))$',
                ],
                options: [
                    'search' => false,
                ]
            )
            ->addCol('pay_type', '支付方式', options: [
                'valueEnum' => getPayType(),
            ])
            ->addCol(
                'order_status',
                '订单状态',
                [
                    'Button',
                    [
                        'size' => 'small',
                        'type' => 'primary',
                        'style' => [
                            'background' => '$Function(recore.order_status == 1 ? "#49a2f1" : (recore.order_status == 2 ? "#37a037" : "#e46a6a"))$',
                            'border' => 'none',
                        ],
                    ],
                    '$text$',
                ],
                options: [
                    'valueEnum' => [
                        0 => '未支付',
                        1 => '已支付',
                        2 => '已完成',
                        -1 => '已取消',
                    ],
                ]
            )
            ->addCol('menu_detail', '菜单详情', options: [
                'search' => false,
                'valueType' => 'code',
            ])
            ->addCol('create_time', '创建时间', options: [
                'search' => false,
            ])
            ->addCol('search_time', '创建时间', options: [
                // 'search' => false,
                'hideInTable' => true,
                'valueType' => 'dateRange',
            ])
            ->addCol('is_takeway', '是否为外卖', options: [
                // 'search' => false,
                'hideInTable' => true,
                'valueEnum' => [
                    0 => '餐厅',
                    1 => '外卖',
                ],
            ])
            ->addButton($viewButton)
            ->addButton($avtiveButton)
            ->addButton($unactiveButton)
            ->addButton($unactiveButton2);

        if ($this->isAdmin()) {
            $table->addButton($delButton);
        }

        $table
            ->addButton($cancelButton)
            ->addButton($recoverButton)
            ->changeApilink('data', $this->router->generate('api_admin_order_list'));

        if ($this->isAdmin()) {
            $table->addToolButton($cleanButton2);
            $table->addToolButton($cleanButton);
            $table->addToolButton($excelButton);
        }

        return $this->tableTemplate($table);
    }

    #[Route('/api/admin/order/list', name: 'api_admin_order_list')]
    public function getList()
    {
        if ($this->isLogin()) {
            // current=1&pageSize=20&desk_status=1
            $paged = $this->request->query->get('current', 1);
            $pageSize = $this->request->query->get('pageSize', 30);
            // $menu_name = $this->request->query->get('menu_name', null);
            $did = $this->request->query->get('oid', null);
            $desk = $this->request->query->get('desk', null);
            $menu_num = $this->request->query->get('order_sn', null);
            $pay_type = $this->request->query->get('pay_type', null);
            $is_delete = $this->request->query->get('order_status', null);
            $timeList = $this->request->query->get('search_time', null);
            $is_takeway = $this->request->query->get('is_takeway', null);

            // $all = Order::init()->with(['details', 'desk'])->createQueryBuilder();
            $all = Order::with('details', 'desk');
            if ($is_delete !== null) {
                $all->where('order_status', $is_delete);
            }

            if ($did !== null && $did > 0) {
                $all->where('oid', $did);
            }

            if ($desk !== null && $desk > 0) {
                $all->where('desk_id', $desk);
            }

            if ($menu_num !== null) {
                $all->where('order_sn', $menu_num);
            }

            if ($timeList !== null) {
                $timeList = explode(',', $timeList);
                $all->whereBetween('create_time', $timeList);
            }

            if ($is_takeway !== null) {
                $all->where('is_takeway', $is_takeway);
            }

            if ($pay_type !== null) {
                $all->where('pay_type', $pay_type);
            }

            $total = $all->count(['*']);
            $all = $all->offset($pageSize * ($paged - 1))->limit($pageSize)->orderBy('oid', 'desc')->get();

            $list = [];
            foreach ($all as $v) {
                $strings = '';
                $details = $v->details;
                if ($details) {
                    foreach ($details as $detail) {
                        $strings .= $detail->menu_name.' x '.$detail->total.PHP_EOL;
                    }
                }

                $v->menu_detail = $strings;
                $list[] = [
                    ...$v->toArray(),
                    'create_time' => $v->create_time->format('Y-m-d H:i:s'),
                ];
            }

            if ($all) {
                $this->addJsonData('data', $list);
                $this->addJsonData('total', $total);

                return $this->sendJson();
            }

            return $this->sendJson('未找到', 404);
        }

        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/admin/order/changeStatus/{oid}', name: 'api_admin_order_change_status')]
    public function changeStatus(int $oid = 0): \Symfony\Component\HttpFoundation\Response
    {
        // $this->addJsonData('title', '修改状态');
        if ($this->isLogin() && $oid) {
            $orderInfo = Order::find($oid);

            if ($orderInfo) {
                $status = $this->request->request->get('status', 0);
                $orderInfo->order_status = $status;
                //
                if ($status == 1) {
                    $orderInfo->pay_time = new DateTime;
                    // 更新桌位
                    $desk = Desk::find($orderInfo->desk_id);
                    if ($desk->use_status == 0) {
                        $desk->use_status = 1;
                        $desk->update();
                    }
                } elseif ($status == 2) {
                    $orderInfo->confirm_time = new DateTime;
                }
                $result = $orderInfo->update();

                if ($result) {
                    if ($status == 2) {
                        $hasCount = Order::where('order_status', '<', '2')->where(['is_delete' => 0, 'is_cancel' => 0, 'desk_id' => $orderInfo->desk_id])->count();

                        if ($hasCount <= 0) {
                            $desk = Desk::find($orderInfo->desk_id);
                            $desk->use_status = 0;
                            $desk->update();
                        }
                    }

                    $this->addJsonData('data', $oid);

                    return $this->sendJson('修改成功');
                }

                return $this->sendJson('修改失败', $oid);
            }

            return $this->sendJson('未找到数据', 404);
        }

        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/admin/order/del/{oid}', name: 'api_admin_order_del')]
    public function delOrder(int $oid = 0)
    {
        // $this->addJsonData('title', '修改状态');
        if ($this->isLogin() && $oid) {
            $orderInfo = Order::find($oid);

            if ($orderInfo) {
                $result = $orderInfo->delete();

                if ($result) {
                    $result = OrderDetail::where([
                        'oid' => $oid,
                    ])->delete();

                    $hasCount = Order::where('order_status', '<', '2')->where(['is_delete' => 0, 'is_cancel' => 0, 'desk_id' => $orderInfo->desk_id])->count();
                    if ($hasCount <= 0) {
                        $desk = Desk::find($orderInfo->desk_id);
                        $desk->use_status = 0;
                        $desk->update();
                    }
                    $this->addJsonData('data', $oid);

                    return $this->sendJson('删除成功');
                }

                return $this->sendJson('修改失败', $oid);
            }

            return $this->sendJson('未找到数据', 404);
        }

        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/admin/order/recover/{oid}', name: 'api_admin_order_del_recover')]
    public function delOrder2(int $oid = 0)
    {
        // $this->addJsonData('title', '修改状态');
        if ($this->isLogin() && $oid) {
            $orderInfo = Order::find($oid);

            if ($orderInfo) {
                $orderInfo->is_cancel = 0;
                $orderInfo->order_status = 0;
                $orderInfo->is_delete = 0;
                $result = $orderInfo->update();
                if ($result) {
                    $desk = Desk::find($orderInfo->desk_id);
                    $desk->use_status = 1;
                    $desk->update();
                    $this->addJsonData('data', $oid);

                    return $this->sendJson('恢复成功');
                }

                return $this->sendJson('修改失败', $oid);
            }

            return $this->sendJson('未找到数据', 404);
        }

        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/admin/order_details/export/{start_time}/{end_time}', name: 'api_admin_order_details_export')]
    public function export2(string $start_time, string $end_time)
    {
        if ($this->isLogin()) {
            // $excel = new ExcelSheet();

            $detailExcel = new ExcelSheet('订单详情');
            $detailExcel->addColumn('order_sn', '订单号');

            $detailExcel->addColumn('desk', '桌号');

            $detailExcel->addColumn('menu_name', '菜品');

            $detailExcel->addColumn('menu_num', '菜品编号');

            $detailExcel->addColumn('total', '数量');

            $detailExcel->addColumn('total_price', '单价');

            $detailExcel->addColumn('price', '价格');

            $detailExcel->addColumn('is_takeway', '是否外卖');

            $detailExcel->addColumn('create_time', '订单创建时间');

            $detailExcel->addColumn('add_time', '点菜时间');

            $orderList = Order::where('create_time', '>=', $start_time)->where('create_time', '<=', $end_time)->get();
            foreach ($orderList as $order) {
                foreach ($order->details as $detail) {
                    $detailExcel->addData([
                        'desk' => $order->desk->desk_name,
                        'is_takeway' => $order->is_takeway,
                        'order_sn' => $order->order_sn,
                        'menu_name' => $detail->menu->menu_name,
                        'menu_num' => $detail->menu->menu_num,
                        'total' => $detail->total,
                        'total_price' => $detail->total_price,
                        'price' => $detail->price,
                        'create_time' => $order->create_time,
                        'add_time' => $detail->add_time,
                    ], [
                        'is_takeway' => ['否', '是'],
                        'is_delete' => ['否', '是'],
                        'is_cancel' => ['否', '是'],
                        'order_status' => ['未支付', '已支付', '已完成'],
                    ]);
                }
            }

            $excelExporter = new ExcelExport('订单列表');

            return $excelExporter->addSheet($detailExcel)->exportFile('订单列表');
        }

        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/admin/order/export/excel', name: 'api_admin_order_export_excel')]
    public function exportExcel()
    {
        if ($this->isLogin()) {
            $did = $this->request->query->get('oid', null);
            $desk = $this->request->query->get('desk', null);
            $menu_num = $this->request->query->get('order_sn', null);
            $is_delete = $this->request->query->get('order_status', null);
            $timeList = $this->request->query->get('search_time', null);
            $is_takeway = $this->request->query->get('is_takeway', null);
            $where = [];
            $order = Order::query();

            if ($is_delete !== null) {
                $order->where('order_status', $is_delete);
            }

            if ($did !== null && $did > 0) {
                $order->where('oid', $did);
            }

            if ($desk !== null && $desk > 0) {
                $order->where('desk_id', $desk);
            }

            if ($menu_num !== null) {
                $order->where('order_sn', $menu_num);
            }

            if ($timeList !== null) {
                [$startTime, $endTime] = explode(',', $timeList);
                $order->whereBetween('create_time', [trim($startTime), trim($endTime)]);
            }

            if ($is_takeway !== null) {
                $order->where('is_takeway', $is_takeway);
            }

            $page = $order->get();

            if ($page) {
                $excelSheet = new ExcelSheet('订单列表');
                $excelSheet
                    ->addColumn('order_sn', '订单编号')
                    ->addColumn('desk_id', '桌号')
                    ->addColumn('pay_price', '支付金额')
                    ->addColumn('note', '订单备注')
                    ->addColumn('is_takeway', '是否为外卖')
                    ->addColumn('takeway_order', '外卖订单号')
                    ->addColumn('order_status', '订单状态')
                    ->addColumn('is_delete', '是否删除')
                    ->addColumn('is_cancel', '是否取消')
                    ->addColumn('confirm_time', '完成时间')
                    ->addColumn('pay_time', '支付时间')
                    ->addColumn('create_time', '创建时间');

                $deskOriginList = Desk::all();
                $deskList = [];
                if ($deskOriginList) {
                    foreach ($deskOriginList as $desk) {
                        $deskList[$desk->getId()] = $desk->desk_name;
                    }
                }

                $excelSheet->addData($page->toArray(), [
                    'desk_id' => $deskList,
                    'is_takeway' => ['否', '是'],
                    'is_delete' => ['否', '是'],
                    'is_cancel' => ['否', '是'],
                    'order_status' => ['未支付', '已支付', '已完成'],
                ]);

                $excelExporter = new ExcelExport('订单列表');

                return $excelExporter->addSheet($excelSheet)->exportFile('订单列表');
            }

            return $this->sendJson('未找到', 404);
        }

        return $this->sendJson('没有权限', 403);
    }
}
