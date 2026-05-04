<?php

namespace App\Entity;

use App\Model\DeskModel;
use App\ORM\Data\DataSource;
use App\ORM\Entity;
use App\ORM\Mapping\Column;
use App\ORM\Mapping\Id;
use App\ORM\Mapping\Join;
use App\ORM\Mapping\PassField;
use App\ORM\Mapping\PassFiled;
use App\ORM\Mapping\Table;
use DateTime;

#[Entity(), Table('res_order')]
class Order extends DataSource
{


    /**
     * 支付方式
     *
     * @var int
     */
    #[Column('integer', 2)]
    private $pay_type = 0;

    /**
     * 确认时间
     *
     * @var string
     */
    #[Column('datetime')]
    private $confirm_time;

    /**
     * 是否删除
     *
     * @var int
     */
    #[Column('string', 20)]
    private $is_delete = 0;

    /**
     * 是否取消
     *
     * @var int
     */
    #[Column('string', 20)]
    private $is_cancel = 0;

    /**
     * 订单状态
     *
     * @var int
     */
    #[Column('string', 20)]
    private $order_status = 0;

    /**
     * 桌号ID
     *
     * @var int
     */
    #[Column('string', 20)]
    private $desk_id;

    /**
     * 是否为拼座
     *
     * @var int
     */
    #[Column('integer', 1)]
    private $is_pin;

    /**
     * 拼桌的数字
     *
     * @var int
     */
    #[Column('integer', 5)]
    private $pin_num;

    /**
     * 支付金额
     *
     * @var int
     */
    #[Column('string', 20)]
    private $pay_price;

    /**
     * 是否为外卖
     *
     * @var int
     */
    #[Column('string', 20)]
    private $is_takeway = 0;
    /**
     * 是否确认上菜
     *
     * @var int
     */
    #[Column('string', 20)]
    private $is_checked = 0;
    /**
     * 是否已读
     */
    #[Column('integer', 1)]
    private $is_read = 0;
    /**
     * 负责的服务员ID
     *
     * @var string
     */
    #[Column('string', 20)]
    private $user_id;

    /**
     * 外卖订单号码
     *
     * @var int
     */
    #[Column('string', 20)]
    private $takeway_order;

    /**
     * 外卖地址
     *
     * @var string
     */
    #[Column('string', 100)]
    private $address;

    /**
     * 免增值税
     *
     * @var string
     */
    #[Column('string', 20)]
    private $is_vat_exempt;

    /**
     * 希望送达时间
     *
     * @var string
     */
    #[Column('string', 50)]
    private $delivery_order_date;

    /**
     * 送餐还是自取
     *
     * @var int
     */
    #[Column('integer', 1)]
    private $is_delivery;

    /**
     *
     * @var string
     */
    #[Column('string', 20)]
    private $realname;

    /**
     *
     * @var string
     */
    #[Column('string', 20)]
    private $phone;

    /**
     *
     * @var string
     */
    #[Column('string')]
    private $note;
    /**
     *
     *
     *
     * @var int
     */
    #[Column('integer'), PassField]
    private $key;
    /**
     *
     *
     * @var [type]
     */
    #[Column('json'), PassField]
    private $desk;
    /**
     *
     *
     *
     * @var int
     */
    #[Column('json'), Join(OrderDetail::class, 'order_detail')]
    private $order_detail = [];

    private string $menu_detail = '';


    public function getOid()
    {
        return $this->oid;
    }

    public function getKey()
    {
        return $this->oid;
    }

    public function getMenuDetail()
    {
        return $this->menu_detail;
    }

    public function setMenuDetail(string $content)
    {
        $this->menu_detail = $content;
        return $this;
    }

    /**
     * @return $this
     */
    public function setOid($oid = 0)
    {
        $this->oid = $oid;
        return $this;
    }

    public function getOrderSn()
    {
        return $this->order_sn;
    }


    /**
     * @return $this
     */
    public function setOrderSn()
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $this->order_sn = 'od' . $now->format('YmdHisu') . rand(1111, 9999);
        return $this;
    }

    public function getCreateTimeOriginal()
    {
        return $this->create_time;
    }

    public function getCreateTime()
    {

        return date('Y-m-d H:i:s', $this->create_time);
    }

    /**
     * @return $this
     */
    public function setCreateTime()
    {
        $this->create_time = time();
        return $this;
    }

    public function getUpdateTimeOriginal()
    {
        return $this->update_time;
    }

    public function getUpdateTime()
    {
        return date('Y-m-d H:i:s', $this->update_time);
    }

    /**
     * @return $this
     */
    public function setUpdateTime()
    {
        $this->update_time = time();
        return $this;
    }

    public function getPayTime()
    {
        return $this->pay_time ? date('Y-m-d H:i:s', $this->pay_time) : null;
    }

    /**
     * @return $this
     */
    public function setPayTime()
    {
        $this->pay_time = time();
        return $this;
    }

    public function getConfirmTime()
    {
        return $this->confirm_time ? date('Y-m-d H:i:s', (int) $this->confirm_time) : '';
    }

    /**
     * @return $this
     */
    public function setConfirmTime()
    {
        $this->confirm_time = time();
        return $this;
    }

    public function getIsDelete()
    {
        return $this->is_delete;
    }

    /**
     * @return $this
     */
    public function setIsDelete($is_delete = 0)
    {
        $this->is_delete = $is_delete;
        return $this;
    }

    public function getIsVatExempt()
    {
        return $this->is_vat_exempt;
    }

    /**
     * @return $this
     */
    public function setIsVatExempt($is_vat_exempt = 0)
    {
        $this->is_vat_exempt = $is_vat_exempt;
        return $this;
    }

    public function getDeliveryOrderDate()
    {
        return $this->delivery_order_date;
    }

    /**
     * @return $this
     */
    public function setDeliveryOrderDate($delivery_order_date = "")
    {
        $this->delivery_order_date = $delivery_order_date;
        return $this;
    }

    public function getIsDelivery()
    {
        return $this->is_delivery;
    }

    /**
     * @return $this
     */
    public function setIsDelivery($is_delivery = 0)
    {
        $this->is_delivery = $is_delivery;
        return $this;
    }

    public function getIsCancel()
    {
        return $this->is_cancel;
    }

    /**
     * @return $this
     */
    public function setIsCancel($is_cancel = 0)
    {
        $this->is_cancel = $is_cancel;
        return $this;
    }

    public function getOrderStatus()
    {
        return $this->order_status;
    }

    /**
     * @return $this
     */
    public function setOrderStatus($order_status = 0)
    {
        $this->order_status = $order_status;

        if ($this->oid > 0) {
            if ($order_status == 1) {
                $this->setPayTime();
            }

            if ($order_status == 2) {
                $this->setConfirmTime();
            }
        }

        return $this;
    }

    public function getDeskId()
    {
        return $this->desk_id;
    }

    /**
     * @return $this
     */
    public function setDeskId($desk_id)
    {
        $this->desk_id = $desk_id;
        return $this;
    }

    public function getIsPin()
    {
        return $this->is_pin;
    }

    /**
     * @return $this
     */
    public function setIsPin($is_pin = 0)
    {
        $this->is_pin = $is_pin;
        return $this;
    }

    public function getPinNum()
    {
        return $this->pin_num;
    }

    // ALTER TABLE `wp_res_order` CHANGE `pin_num` `pin_num` TINYINT NOT NULL DEFAULT '0';
    /**
     * @return $this
     */
    public function setPinNum($is_pin = 0)
    {
        $this->pin_num = $is_pin;
        return $this;
    }

    public function getPayPrice()
    {
        return $this->pay_price;
    }

    /**
     * @return $this
     */
    public function setPayPrice($pay_price = 0)
    {
        $this->pay_price = $pay_price;
        return $this;
    }

    public function getIsTakeway()
    {
        return $this->is_takeway;
    }

    /**
     * @return $this
     */
    public function setIsTakeway($is_takeway = 0)
    {
        $this->is_takeway = $is_takeway;
        return $this;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return $this
     */
    public function setUserId($user_id = 0)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getTakewayOrder()
    {
        return $this->takeway_order;
    }

    /**
     * @return $this
     */
    public function setTakewayOrder($takeway_order = 0)
    {
        $this->takeway_order = $takeway_order;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return $this
     */
    public function setIsChecked($is_checked = 0)
    {
        $this->is_checked = $is_checked;
        return $this;
    }

    public function getIsChecked()
    {
        return $this->is_checked;
    }

    /**
     * @return $this
     */
    public function setAddress($address = 0)
    {
        $this->address = $address;
        return $this;
    }

    public function getOrderDetail()
    {
        return $this->order_detail;
    }

    /**
     * @return $this
     */
    public function setOrderDetail($address = [])
    {
        $this->order_detail = $address;
        return $this;
    }

    public function getRealname()
    {
        return $this->realname;
    }

    /**
     * @return $this
     */
    public function setRealname($realname = "")
    {
        $this->realname = $realname;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return $this
     */
    public function setNote($note = "")
    {
        $this->note = $note;
        return $this;
    }



    public function getPayType()
    {
        return $this->pay_type;
    }

    /**
     * @return $this
     */
    public function setPayType($pay_type = 0)
    {
        $this->pay_type = $pay_type;
        return $this;
    }

    public function getIsRead()
    {
        return $this->is_read;
    }

    /**
     * @return $this
     */
    public function setIsRead($read = 0)
    {
        $this->is_read = $read;
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return $this
     */
    public function setPhone($phone = "")
    {
        $this->phone = $phone;
        return $this;
    }

    public function getDesk()
    {
        $desk = new DeskModel();
        return $desk->findOneBy($this->desk_id);
    }
}
