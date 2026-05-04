<?php

namespace App\Model;

use DateTime;
use Dbout\WpOrm\Orm\AbstractModel;
use Dbout\WpOrm\Orm\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * 菜单
 *
 * @property int $id 菜单ID
 * @property string $menu_name 菜单名称
 * @property string $menu_subname 菜单名称
 * @property string $menu_note 菜单备注
 * @property string $menu_num 菜单编号
 * @property float $menu_price 菜单价格
 * @property int $menu_count 菜单数量
 * @property int $menu_sales 菜单销量
 * @property int $menu_category 菜单分类
 * @property DateTime $add_time 添加时间
 * @property int $is_delete 是否删除
 * @property int $status 状态
 * @property int $is_attr 是否属性
 * @property int $is_hot 是否热卖
 * @property int $out_site_id 外卖
 * @method static Builder|Order where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|Order whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Order whereOr($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Order whereHas($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Order whereNull($columns, $boolean = 'and', $not = false)
 * @method static Builder|Order whereNotNull($columns, $boolean = 'and')
 * @method static Builder|Order whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method static Builder|Order whereBetween($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Order find($id)
 * @method static Builder|Order orderBy($column, $direction = 'asc')
 * @method static Builder|Order query()
 * @method static Builder|Order select($columns = ['*'])
 */
class Menu extends AbstractModel
{
    protected $table = 'res_menu';
    public $timestamps = false;

    protected $fillable = [
        'menu_name',
        'menu_subname',
        'menu_note',
        'menu_num',
        'menu_price',
        'menu_count',
        'menu_sales',
        'menu_category',
        'add_time',
        'is_delete',
        'status',
        'is_attr',
        'is_hot',
        'out_site_id',
    ];

    protected $attributes = [
        'is_delete' => 0,
        'status' => 0,
        'is_attr' => 0,
        'is_hot' => 0,
        'out_site_id' => 0,
    ];

    protected $casts = [
        'menu_price' => 'decimal:2',
        'menu_count' => 'integer',
        'menu_sales' => 'integer',
        'menu_category' => 'integer',
        'is_delete' => 'integer',
        'status' => 'integer',
        'is_attr' => 'integer',
        'is_hot' => 'integer',
        'out_site_id' => 'integer',
        'add_time' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'mid',
        'key'
    ];
    public function getMidAttribute(): int
    {
        return $this->id;
    }

    public function getKeyAttribute(): int
    {
        return $this->id;
    }
}
