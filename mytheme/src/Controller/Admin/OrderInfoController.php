<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Model\Desk;
use App\Model\Menu;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Service\AdminMessage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class OrderInfoController extends Wordpress
{
    #[Route('/adminpanel/order/view/{oid}', defaults: ['oid' => 0], name: 'admin_order_info')]
    public function list($oid = 0)
    {
        $orderModel = new Order();
        $order = $orderModel->find($oid);
        if ($order) {
            if ($order->is_read != 1) {
                // $order->update_time = new \DateTime();
                $order->is_read = 1;
                $order->update();
            }
        }
        return $this->render('admin/orderInfo.html.twig', [
            'title' => '订单详情',
            'siteConfig' => [],
        ]);
    }

    #[Route('/api/order/discount/{oid}', name: 'api_admin_set_order_discount')]
    public function setDiscount($oid = 0): Response
    {
        if ($this->isLogin()) {
            if ($oid > 0) {
                $order = Order::find($oid);
                if ($order) {
                    $discount = $this->request->request->get('discount');
                    $order->pay_discount = $discount <= 0 ? 0 : $discount;
                    // $order->update_time = new \DateTime();
                    $res = $order->update();
                    if ($res) {
                        return $this->sendJson('设置成功', 200);
                    }
                    return $this->sendJson('设置失败', 500);
                }
            }
            return $this->sendJson('订单号错误', 404);
        }
        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/admin/order_detail/{oid}', name: 'api_admin_get_order_detail')]
    public function getDetail2(mixed $oid = 0): Response
    {
        if (!empty($oid) && $this->isLogin()) {

            $order = Order::find($oid);
            $detailList = [];
            if ($order) {
                // $order->create_time = $order->create_time->addSeconds($order->create_time->getOffset());
                if ($order->is_read != 1) {
                    $order->is_read = 1;
                    $order->update();
                }
                foreach ($order->details as $detail) {
                    $detailList[] = $detail;
                }
                $desk = Desk::find($order->desk_id);

                $this->addJsonData('data', [
                    'order' => [
                        ...$order->toArray(),
                        'desk' => [
                            // ...$order->desk->toArray(),
                            ...$desk->toArray(),
                            'desk_name' => $desk->desk_name . ($order->is_pin ?  ':' . $order->pin_num : ''),
                        ],
                        'create_time' => $order->create_time->format('Y-m-d H:i:s'),
                    ],
                    'detail' => $this->changeMenuId($detailList ?: []),
                    'payInfo' => getSelectPayType(),
                    // 'showDiscount' => defined('SHOW_DISCOUNT'),
                    'defaultPayInfo' => getDefaultPayType(),
                ]);
                return $this->sendJson();
            }
        }

        return $this->sendJson('未知订单号', 404);
    }

    private function changeMenuId($data): array
    {
        $newData = [];
        foreach ($data as $item) {
            // $item = $item->toArray();
            $menu = $item->menu;
            $menu_price = $menu->menu_price;
            // if (defined('SHOW_DISCOUNT')) {
            //   $item['menu']['menu_price'] = (new \App\Model\MenuDiscount())->getDiscountPriceByIdPrice($item['menu_id'], $item['menu_price']);
            // }

            $newData[] = [
                ...$item->toArray(),
                'menu_id' => [
                    'menu_num' => $item->menu_id,
                    'mid' => $item->menu_id,
                    'menu_price' => number_format($menu_price, 2, '.', ''),
                    'menu_name' => $menu->menu_name,
                    'total' => $item->total,
                ],
            ];
        }
        return $newData;
    }

    #[Route('/api/user/update-count/{odid}', name: 'add_count_oid2')]
    public function addCount($odid = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($odid > 0 && $this->isLogin()) {
            $add_count = $this->request->request->get('add_count');
            /** @var OrderDetail $detail */
            $detail = OrderDetail::find($odid);

            if ($detail->exists) {
                $order = Order::find($detail->oid);

                if ($order->order_status == 2) {
                    return $this->sendJson('订单已完成', 500);
                }
                $oldCount = $detail->add_count;
                $detail->add_count = $add_count;
                if ($detail->update()) {
                    $menu = Menu::find($detail->menu_id);
                    $menu->menu_sales += $add_count - $oldCount;
                    $menu->update();
                    return $this->sendJson('保存成功', 200);
                }
                return $this->sendJson('更新失败', 500);
            }
        }
        return $this->sendJson('未找到数据', 404);
    }

    private function getPrice($menuData)
    {
        $price = 0;
        foreach ($menuData as $menu) {
            if ($menu['is_delete'] === false) {
                $price += $menu['price'] * $menu['total'];
            }
        }

        return $price;
    }

    #[Route('/api/admin/orderAddMenu/{oid}', name: 'add_order_menu_from_admin')]
    public function addMenus(AdminMessage $message, $oid = 0)
    {
        if ($this->isLogin() && $oid > 0) {
            $order = Order::find($oid);
            $desk = Desk::find($order->desk_id);

            if ($order) {
                // $all_price = $this->request->request->get('all_price', '0.00');
                // $order->update_time = new \DateTime();
                $order->pay_price = $this->getPrice($this->request->request->all('menus') ?: []);
                if ($order->is_checked == 0) {
                    $order->is_checked = 1;
                }

                if ($order->update()) {
                    $menus = $this->request->request->all('menus');

                    foreach ($menus as $item) {
                        if ($item['odid'] != 0 && $item['is_delete'] == 1) {
                            OrderDetail::where('odid', $item['odid'])->delete();
                            continue;
                        }
                        if ($item['odid'] != 0) {
                            $orderDetail = OrderDetail::find($item['odid']);
                            if ($orderDetail->total !== $item['total']) {
                                $orderDetail->add_time = new \DateTime();
                            }
                        } else {
                            $orderDetail = new OrderDetail();
                            $orderDetail->add_time = new \DateTime();
                            $orderDetail->oid = $oid;
                            $orderDetail->add_count = 0;
                            $orderDetail->total_price = $item['price'];
                            $orderDetail->menu_name = $item['name'];
                            $orderDetail->menu_id = $item['mid'];
                        }

                        $orderDetail->total = $item['total'];
                        $orderDetail->note = $item['note'];
                        $orderDetail->setPrice();

                        // ->setIsDelete($item['is_delete'] ? 1 : 0);
                        if ($orderDetail->exists) {
                            $orderDetail->update();
                        } else {
                            $orderDetail->save();
                        }
                    }
                    // $message->addMessage('订单修改通知', '有订单加菜', musicFile: $this->getOption('takeway_type3_audio'));
                    $message->addMessage(
                        '订单修改通知',
                        ($desk->is_takeway == 0 ? '桌位 ' : '网站 ')
                            . $desk->menu_guid
                            . ($order->pin_num > 0
                                ? ($desk->is_takeway == 0 ? '-' : '_') . $order->pin_num
                                : '')
                            . ' 订单内容有修改',
                        musicFile: $this->getOption('takeway_type3_audio')
                    );
                    $this->addJsonData('links', $this->generateUrl('admin_system_tabs_show', [
                        'name' => 'OrderSystem',
                        'method' => 'list',
                    ]));
                    return $this->sendJson('修改成功', 200);
                }

                return $this->sendJson(
                    '修改失败',
                    500,
                );
            }
            return $this->sendJson(
                '没有找到订单',
                404,
            );
        }
        return $this->sendJson(
            '没有权限',
            500,
        );
    }

    #[Route('/api/order/cancel/{order_id}', name: 'api_admin_cancel_order2')]
    public function cancelOrder($order_id = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $order_id > 0) {
            $order = Order::find($order_id);

            if ($order && $order->is_cancel == 0) {
                $order->is_cancel = 1;
                $order->order_status = -1;
                // $order->update_time = new \DateTime();
                $res = $order->update();

                if ($res !== false) {
                    $hasCount = Order::where(['is_delete' => 0, 'is_cancel' => 0, 'desk_id' => $order->getDeskId()])
                        ->where('order_status', '<', '2')
                        ->count();
                    if ($hasCount <= 0) {
                        $desk = Desk::find($order->desk_id);
                        $desk->use_status = 0;
                        $desk->update();
                    }

                    return $this->sendJson('已取消', 200);
                }
            }
            return $this->sendJson('没有此订单', 404);
        }
        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/order/confirm/{order_id}', name: 'api_admin_confirm_order2')]
    public function confirmOrder($order_id = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $order_id > 0) {
            $order = Order::find($order_id);

            if ($order && $order->order_status < 2) {
                $order->order_status = 2;
                $order->confirm_time = new \DateTime();
                $order->is_cancel = 0;
                $order->is_delete = 0;
                $res = $order->update();

                //                $details = OrderDetail::where('oid' , $order->oid)->get();

                // foreach ($details as $detail) {
                //     $detail->setTotal($detail->getAddCount());
                //     $orderDetail->update($detail);
                // }
                if ($res) {
                    $hasCount = Order::where('order_status', '<', '2')->where(['is_delete' => 0, 'is_cancel' => 0, 'desk_id' => $order->desk_id])->count();

                    if ($hasCount <= 0) {
                        $desk = Desk::find($order->desk_id);
                        $desk->use_status = 0;
                        $desk->update();
                    }

                    return $this->sendJson('已取消', 200);
                }
            }
            return $this->sendJson('没有此订单', 404);
        }
        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/order/pay/{order_id}', name: 'api_admin_is_pay_order2')]
    public function payOrderData($order_id = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $order_id > 0) {
            $order = Order::find($order_id);
            if ($order) {
                $order->pay_time = new \DateTime();
                $order->order_status = 1;
                $order->pay_type = $this->request->get('pay_type', getDefaultPayType());
                $res = $order->update();

                if ($res !== false) {
                    return $this->sendJson('支付成功', 200);
                }
            }
            return $this->sendJson('没有此订单', 404);
        }
        return $this->sendJson('没有权限', 403);
    }

    #[Route('/api/user/order_item/all_update/{oid}', name: 'api_admin_update_all_order_item_menu')]
    public function updateAllOrderItem($oid = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $oid > 0) {
            $order = OrderDetail::where('oid', $oid)->get();

            if ($order) {
                foreach ($order as $detail) {
                    $detail->setAddCount($detail->getTotal());
                    $detail->add_count = $detail->total;
                    try {
                        $detail->update();
                    } catch (Exception) {
                        return $this->sendJson('保存失败', 500);
                    }
                }
                return $this->sendJson('操作成功', 200);
            }
            return $this->sendJson('未知订单号', 404);
        }
        return $this->sendJson('没有权限访问', 403);
    }

    #[Route('/api/user/order_item/delete/{oid}/{odid}', name: 'api_admin_delete_order_item_menu')]
    public function deleteOrderItem($oid = 0, $odid = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $oid > 0 && $odid > 0) {
            $order = Order::find($oid);

            if ($order) {
                $orderItem = OrderDetail::where([
                    'odid' => $odid,
                    'oid' => $oid,
                ])->first();

                if ($orderItem) {
                    $order->pay_price = $order->pay_price - ($orderItem->total_price * $orderItem->total);
                    $orderItem->delete();
                    // $order->update_time = new \DateTime();
                    $res = $order->update();
                    if ($res) {
                        return $this->sendJson('操作成功', 200);
                    }
                    return $this->sendJson('保存失败', 500);
                }
                return $this->sendJson('未知菜单项', 404);
            }
            return $this->sendJson('未知订单号', 404);
        }
        return $this->sendJson('没有权限访问', 403);
    }

    /**
     * @
     */
    #[Route('/api/user/order/checked/{oid}', name: 'checked_order')]
    public function checkedOrderItem($oid = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $oid > 0) {
            $order = Order::find($oid);

            if ($order) {
                $order->is_checked = 1;
                //$order->update_time = new \DateTime();
                $order->update();
                return $this->sendJson('确认完成', 200);
            }
            return $this->sendJson('未知订单号', 404);
        }
        return $this->sendJson('没有权限访问', 403);
    }

    #[Route('/api/order/edit/{order_id}', name: 'api_admin_edit_orders')]
    public function editAdminOrder($order_id = 0): \Symfony\Component\HttpFoundation\Response
    {
        if ($this->isLogin() && $order_id > 0) {
            $order = Order::find($order_id);

            if ($order) {
                $note = $this->request->request->get('note', $order->note);
                $payType = $this->request->request->get('pay_type', $order->pay_type);
                $realname = $this->request->request->get('realname', $order->realname);
                $phone = $this->request->request->get('phone', $order->phone);
                $address = $this->request->request->get('address', $order->address);
                $is_take = $this->request->request->get('is_takeway', $order->is_take);
                $desk_id = $this->request->request->get('desk_id', $order->desk_id);

                $order->note = empty($note) ? $order->note : $note;
                $order->is_takeway = $is_take;
                $order->realname = empty($realname) ? $order->realname : $realname;
                $order->phone = empty($phone) ? $order->phone : $phone;
                $order->pay_type = $payType;
                $order->address = empty($address) ? $order->address : $address;

                $desk_id = empty($desk_id) ? $order->desk_id : $desk_id;

                if ($desk_id > 0 && $desk_id != $order->desk_id) {
                    $order->desk_id = $desk_id;
                    $deskOrderCount = Order::where([
                        'desk_id' => $desk_id,
                        'is_delete' => 0,
                        'is_cancel' => 0
                    ])
                        ->where('order_status', '<', 2)
                        ->count();
                    if ($deskOrderCount >= 1) {
                        $lastCount = Order::where([
                            'desk_id' => $desk_id,
                            'is_delete' => 0,
                            'is_cancel' => 0,
                            'pin_num' => $deskOrderCount
                        ])
                            ->where('order_status', '<', 2)
                            ->count();

                        if ($lastCount >= 1) {
                            $last = Order::where(['desk_id' => $desk_id, 'is_delete' => 0, 'is_cancel' => 0])->where('order_status', '<', 2)->orderBy('pin_num', 'desc')->first();
                            $deskOrderCount = $last->pin_num + 1;
                        }

                        $order->is_pin = 1;
                        $order->pin_num = $deskOrderCount;
                    } else {
                        $order->is_pin = 0;
                        $order->pin_num = 0;
                    }
                }

                if ($order->update()) {
                    return $this->sendJson('修改成功', 200);
                }

                return $this->sendJson('修改失败', 500);
            }

            return $this->sendJson('没有找到订单', 404);
        }
        return $this->sendJson('没有权限', 500);
    }
}
