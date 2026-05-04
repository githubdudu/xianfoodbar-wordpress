<?php

namespace App\Entity;

use App\Core\Model\Entity;
use App\ORM\Data\DataSource;
use App\ORM\Entity as ORMEntity;
use App\ORM\Mapping\Column;
use App\ORM\Mapping\Id;
use App\ORM\Mapping\PassField;
use App\ORM\Mapping\Table;

#[ORMEntity(), Table('res_menu')]
class Menu extends DataSource
{
    /**
     * 菜单主键
     *
     */

    #[
        Id,
        Column('integer', 11),
    ]
    private ?int $id = null;


    private ?int $mid = null;
    /**
     * Undocumented variable
     *
     *
     * @var [type]
     */
    #[Column('string'), PassField]
    private int $key;

    /**
     * 菜单名称
     *
     * @var string
     */
    #[Column(length: 100)]
    private string $menu_name;

    /**
     * 菜单名称
     *
     * @var string
     */
    #[Column(length: 255)]
    private string $menu_subname;

    /**
     * 菜单名称
     *
     * @var string
     */
    #[Column()]
    private string $menu_note;

    /**
     *
     * @var string
     */
    #[Column(length: 40)]
    private string $menu_num;

    /**
     * 菜品价格
     *
     * @var float
     */
    #[Column('string', length: 13)]
    private $menu_price;

    /**
     * 菜品剩余数量
     *
     * @var integer
     */
    #[Column('integer', 7)]
    private int $menu_count = 100;

    /**
     * 菜品剩余数量
     *
     * @var integer
     */
    #[Column('integer', 11)]
    private int $menu_sales = 0;

    /**
     * 菜品剩余数量
     *
     * @var integer
     */
    #[Column('integer', 11)]
    private int $menu_category = 0;

    /**
     * 添加时间
     *
     * @var datetime
     */
    #[Column('datetime')]
    private $add_time;

    /**
     * 是否已删除
     *
     * @var integer
     */
    #[Column('integer', 2)]
    private $is_delete = 0;

    /**
     * 附加状态
     *
     * @var integer
     */
    #[Column('integer', 2)]
    private $status = 0;

    /**
     * 附加状态
     *
     * @var integer
     */
    #[Column('integer', 2)]
    private $is_attr = 0;

    /**
     * 是否热门
     *
     * @var integer
     */
    #[Column('integer', 2)]
    private $is_hot = 0;

    /**
     * 附加状态
     *
     * @var integer
     */
    #[Column('integer', 11)]
    private $out_site_id = 0;

    /**
     * 获取ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMid()
    {
        return $this->id;
    }

    /**
     * 获取ID
     *
     * @return string
     */
    public function getKey()
    {
        return $this->id;
    }

    /**
     * 设置id
     *
     * @param integer $id
     * @return $this
     */
    public function setId(int $id = 0)
    {
        $this->id = $id;
        return $this;
    }


    /**
     * 获取菜品名称
     *
     * @return string
     */
    public function getMenuName()
    {
        return $this->menu_name;
    }

    /**
     * 设置菜品名称
     * @param string $name
     * @return $this
     */
    public function setMenuName(string $name = '')
    {
        $this->menu_name = $name;
        return $this;
    }

    /**
     * 获取菜品名称
     *
     * @return string
     */
    public function getMenuSubname()
    {
        return $this->menu_subname;
    }

    /**
     * 设置菜品名称
     * @param string $name
     * @return $this
     */
    public function setMenuSubname(string $name = '')
    {
        $this->menu_subname = $name;
        return $this;
    }

    /**
     * 获取菜品名称
     *
     * @return string
     */
    public function getMenuNote()
    {
        return $this->menu_note;
    }

    /**
     * 设置菜品名称
     * @param string $note
     * @return $this
     */
    public function setMenuNote(string $note = '')
    {
        $this->menu_note = $note;
        return $this;
    }

    /**
     * 获取菜品名称
     *
     * @return string
     */
    public function getMenuNum()
    {
        return $this->menu_num;
    }

    /**
     * 设置菜品名称
     * @param string $name
     * @return $this
     */
    public function setMenuNum(string $menu_num = '')
    {
        $this->menu_num = $menu_num;
        return $this;
    }


    /**
     * 获取菜品数量
     *
     * @return int
     */
    public function getMenuCategory()
    {
        return $this->menu_category;
    }

    /**
     * 设置菜品数量
     *
     * @param integer $count
     * @return $this
     */
    public function setMenuCategory(int $menu_category = 0)
    {
        $this->menu_category = $menu_category;
        return $this;
    }

    /**
     * 获取菜品数量
     *
     * @return int
     */
    public function getMenuCount()
    {
        return $this->menu_count;
    }

    /**
     * 设置菜品数量
     *
     * @param integer $count
     * @return $this
     */
    public function setMenuCount(int $count = 100)
    {
        $this->menu_count = $count;
        return $this;
    }

    /**
     * 获取菜品数量
     *
     * @return int
     */
    public function getMenuSales()
    {
        return $this->menu_sales;
    }

    /**
     * 设置菜品数量
     *
     * @param integer $count
     * @return $this
     */
    public function setMenuSales(int $count = 100)
    {
        $this->menu_sales += $count;
        return $this;
    }

    /**
     * 获取菜品价格
     *
     * @return string
     */
    public function getMenuPrice()
    {
        return $this->menu_price;
    }

    /**
     * 设置菜品价格
     * @param string|int $price
     * @return $this
     */
    public function setMenuPrice($price = '')
    {
        $this->menu_price = \number_format($price, 2, '.', '');
        return $this;
    }


    /**
     * 获取格式化后时间
     *
     * @return string
     */
    public function getAddTime()
    {
        return date('Y-m-d H:i', (int)$this->add_time);
    }

    /**
     * 设置时间
     *
     * @param string|int $time
     * @return $this
     */
    public function setAddTime()
    {
        $this->add_time = time();
        return $this;
    }


    /**
     * 是否已删除
     *
     * @return string
     */
    public function getIsDelete()
    {
        return $this->is_delete;
    }

    /**
     * 设置是否已删除
     * @param integer $is_delete
     * @return $this;
     */
    public function setIsDelete($is_delete = 0)
    {
        $this->is_delete = $is_delete;
        return $this;
    }


    /**
     * 获取状态
     *
     * @return string
     */
    public function getStatus()
    {
        $status = ['已上架', '已下架', '已售罄'];
        return $status[$this->status];
    }

    /**
     * 设置状态
     *
     * @param integer $status
     * @return $this
     */
    public function setStatus($status = 0)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * 获取状态
     *
     * @return string
     */
    public function getIsAttr()
    {
        return $this->is_attr;
    }

    /**
     * 设置状态
     *
     * @param integer $status
     * @return $this
     */
    public function setIsAttr($is_attr = 0)
    {
        $this->is_attr = $is_attr;
        return $this;
    }

    /**
     * 获取状态
     *
     * @return string
     */
    public function getOutSiteId()
    {
        return $this->out_site_id;
    }

    /**
     * 设置状态
     *
     * @param integer $status
     * @return $this
     */
    public function setOutSiteId($out_site_id = 0)
    {
        $this->out_site_id = $out_site_id;
        return $this;
    }
}
