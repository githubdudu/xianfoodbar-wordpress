<?php

namespace App\Model;

use App\Core\DateTime;
use Dbout\WpOrm\Orm\AbstractModel;
use Dbout\WpOrm\Orm\Builder;

/**
 * 订单详情
 * $menu_name $total_price $add_count
 * @property int $odid 订单详情ID
 * @property int $oid 订单ID
 * @property int $menu_id 菜品ID
 * @property int $total 数量
 * @property float $price 价格
 * @property string $note 备注
 * @property \DateTime $add_time 添加时间
 * @property string $menu_name 菜品名称
 * @property float $total_price 单价
 * @property int $add_count 上菜数量
 * @property int $is_delete 是否删除
 * @method static Builder|OrderDetail where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|OrderDetail whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|OrderDetail whereOr($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|OrderDetail whereHas($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|OrderDetail whereNull($columns, $boolean = 'and', $not = false)
 * @method static Builder|OrderDetail whereNotNull($columns, $boolean = 'and')
 * @method static Builder|OrderDetail whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method static Builder|OrderDetail whereBetween($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|OrderDetail find($id)
 * @method static Builder|OrderDetail orderBy($column, $direction = 'asc')
 * @method static Builder|OrderDetail query()
 * @method static Builder|OrderDetail select($columns = ['*'])
 */
class OrderDetail extends AbstractModel
{
  protected $primaryKey = 'odid';

  protected $table = 'res_order_detail';

  public $timestamps = false;

  protected $fillable = [
    'oid',
    'menu_id',
    'total',
    'price',
    'note',
    'add_time',
    'menu_name',
    'total_price',
    'add_count',
    'is_delete',
  ];

  protected $attributes = [
    'is_delete' => 0,
    'add_count' => 0,
    'total' => 1,
  ];

  protected $appends = [
    'key',
    'menu_data'
  ];

  protected $casts = [
    // 'add_time' => 'datetime',
    'total_price' => 'float',
    'add_count' => 'integer',
    'is_delete' => 'boolean',
    'price' => 'float',
    'total' => 'integer',
  ];

  public function getKeyAttribute()
  {
    return $this->odid;
  }

  public function order()
  {
    return $this
      ->belongsTo(Order::class, 'oid', 'oid')
      ->where('order_status', '<', 2)
      ->where('is_delete', 0)
      ->where('is_cancel', 0)
      ->where('is_checked', 1);
  }

  public function menu()
  {
    return $this->belongsTo(Menu::class, 'menu_id', 'id');
  }

  public function desk()
  {
    return $this->belongsTo(Desk::class, 'id', 'desk_id');
  }

  public function setPrice(): void
  {
    $this->price = $this->total * $this->total_price;
  }
}
