<?php

namespace App\Entity;

use App\Model\DeskModel;
use App\Model\ResMenuModel;
use App\ORM\Data\DataSource;
use App\ORM\Entity;
use App\ORM\Mapping\Column;
use App\ORM\Mapping\Id;
use App\ORM\Mapping\Join;
use App\ORM\Mapping\Table;

#[Entity(), Table('res_order_detail')]
class OrderDetail extends DataSource
{
    /**
     * 主键
     *
     *
     * @var int
     */
    #[Id, Column('integer', 11)]
    private ?int $odid = null;
    /**
     * 订单ID
     *
     * @var int
     */
    #[Column('integer', 11)]
    private int $oid;
    /**
     * 菜品ID
     *
     * @var int
     */
    #[Column('integer', 11)]
    private int $menu_id;
    /**
     * 数量
     *
     * @var int
     */
    #[Column('integer', 10)]
    private int $total = 1;
    /**
     * 价格
     *
     * @var float
     */
    #[Column('float', '10,2')]
    private $price;
    /**
     * 备注
     *
     * @var float
     */
    #[Column('string')]
    private $note;
    /**
     * 添加时间
     *
     * @var int
     */
    #[Column('datetime')]
    private $add_time;

    /**
     * 菜品名称（用作偷懒）
     *
     * @var int
     */
    #[Column('string', 255)]
    private $menu_name;
    /**
     * 单价
     *
     * @var int
     */
    #[Column('float', '10,2')]
    private $total_price;
    /**
     * 是否取消
     *
     * @var int
     */
    #[Column('integer', 1)]
    private $is_delete = 0;
    /**
     * 上菜数量
     *
     * @var int
     */
    #[Column('integer', 11)]
    private $add_count = 0;
    /**
     *
     * @var ResMenuModel
     */
    private $menu;
    /**
     * @var Order
     */
    #[Column('json'), Join(Order::class, 'order')]
    private $order;
    /**
     *
     *
     *
     * @var int
     */
    private $key;

    public function init()
    {
    }


    public function getOdid()
    {
        return $this->odid;
    }

    public function getKey()
    {
        return $this->odid;
    }

    public function setOdid($odid = 0)
    {
        $this->odid = $odid;
        return $this;
    }

    public function getOid()
    {
        return $this->oid;
    }

    public function setOid($oid = 0)
    {
        $this->oid = $oid;
        return $this;
    }

    public function getOringalMenuId()
    {
        return $this->menu_id;
    }

    public function getMenuId()
    {
        $temp = (new ResMenuModel());
        $data = $temp->findOneBy('id', $this->menu_id);
        return $data;
    }

    public function getOriginalMenuId()
    {
        return $this->menu_id;
    }

    public function setMenuId($menu_id = 0)
    {
        $this->menu_id = $menu_id;
        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total = 0)
    {
        $this->total = $total;
        return $this;
    }

    public function getNote()
    {
        // var_dump(html_entity_decode($this->note, ENT_HTML5));
        return html_entity_decode($this->note, ENT_HTML5);
    }

    public function setNote($note = "")
    {
        $this->note = $note;
        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price = 0)
    {
        $this->price = $this->total * $this->total_price;
        return $this;
    }

    public function getOringAddTime()
    {
        return $this->add_time;
    }

    public function getAddTime()
    {
        return date('Y-m-d H:i:s', $this->add_time);
    }

    public function setAddTime()
    {
        $this->add_time = time();
        return $this;
    }

    public function getMenuName()
    {
        return $this->menu_name;
    }

    public function setMenuName($menu_name = 0)
    {
        $this->menu_name = $menu_name;
        return $this;
    }

    public function getTotalPrice()
    {
        return $this->total_price;
    }

    public function setTotalPrice($total_price = 0)
    {
        $this->total_price = $total_price;
        return $this;
    }

    public function getIsDelete()
    {
        return $this->is_delete;
    }

    public function setIsDelete($is_delete = 0)
    {
        $this->is_delete = $is_delete;
        return $this;
    }

    public function getAddCount()
    {
        return $this->add_count;
    }

    public function setAddCount($add_count = 1)
    {
        $this->add_count = $add_count;
        return $this;
    }

    /**
     * 获取到Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * 设置Order内容
     *
     * @param Order|null $order
     * @return $this
     */
    public function setOrder(?Order $order)
    {
        $this->setOrder($order);
        return $this;
    }
}
