<?php

namespace App\Model;

use Dbout\WpOrm\Orm\AbstractModel;
use Dbout\WpOrm\Orm\Builder;
use DateTime;

/**
 * 订单列表
 * $order_detail
 * @property int $oid 订单ID
 * @property string $order_sn 订单编号
 * @property DateTime $create_time 创建时间
 * @property DateTime $update_time 更新时间
 * @property DateTime $pay_time 支付时间
 * @property DateTime $confirm_time 确认时间
 * @property int $pay_type 支付方式
 * @property int $pay_discount 支付折扣
 * @property int $is_delete 是否删除
 * @property int $is_cancel 是否取消
 * @property int $order_status 订单状态
 * @property int $desk_id 桌号ID
 * @property int $is_read 是否已读
 * @property int $is_pin 是否为拼座
 * @property int $pin_num 拼桌的数字
 * @property int $pay_price 支付金额
 * @property int $is_takeway 是否为外卖
 * @property int $is_checked 是否已经检查
 * @property int $user_id 负责的服务员ID
 * @property string $takeway_order 外卖订单编号
 * @property string $address 送餐地址
 * @property string $is_vat_exempt 是否含税
 * @property string $delivery_order_date 配送日期
 * @property string $is_delivery 是否配送
 * @property string $realname 联系人
 * @property string $phone 联系电话
 * @property string $note 备注
 * @property int $key 订单关键字
 * @property-read Desk $desk
 * @property-read OrderDetail[] $details
 * @property-read OrderDetail[] $detailsMenu
 *
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
class Order extends AbstractModel
{
  protected $table = 'res_order';
  protected $primaryKey = 'oid';

  protected $fillable = [
    'order_sn',
    'pay_time',
    'pay_type',
    'confirm_time',
    'is_delete',
    'is_cancel',
    'order_status',
    'desk_id',
    'is_pin',
    'pin_num',
    'pay_price',
    'is_takeway',
    'is_checked',
    'user_id',
    'takeway_order',
    'address',
    'is_vat_exempt',
    'delivery_order_date',
    'is_delivery',
    'realname',
    'phone',
    'note',
  ];

  protected function casts(): array
  {
    return [
      'is_delete' => 'integer',
      'is_cancel' => 'integer',
      'order_status' => 'integer',
      'is_pin' => 'integer',
      'is_takeway' => 'integer',
      'is_checked' => 'integer',
      'is_vat_exempt' => 'integer',
      'is_delivery' => 'integer',
      'pin_num' => 'integer',
      'pay_price' => 'decimal:2',
      'desk_id' => 'integer',
      'user_id' => 'integer',
      'pay_type' => 'integer',
      // 'pay_discount' => 'integer',
      'pay_time' => 'datetime',
      'confirm_time' => 'datetime',
      'is_read' => 'integer',
      'create_time' => 'datetime',
      'update_time' => 'datetime',
    ];
  }

  protected $attributes = [
    'is_delete' => 0,
    'is_cancel' => 0,
    'order_status' => 0,
    'is_takeway' => 0,
    'pin_num' => 0,
    'pay_type' => 0,
    'is_read' => 0,
  ];

  const UPDATED_AT = 'update_time';
  const CREATED_AT = 'create_time';

  protected $appends = [
    'key',
    'desk_name'
  ];

  public function desk()
  {
    return $this->belongsTo(Desk::class, 'desk_id', 'id');
  }

  public function details()
  {
    return $this->hasMany(OrderDetail::class, 'oid', 'oid');
  }

  public function getKeyAttribute(): mixed
  {
    return $this->{$this->primaryKey};
  }

  public function getDeskNameAttribute()
  {
    if (isset($this->desk_id) && !empty($this->desk_id)) {
      return Desk::find($this->desk_id)?->desk_name;
    }
    return null;
  }

  //    public function getOrderDetailAttribute()
  //    {
  //        return OrderDetail::where('oid', $this->oid)->get();
  //    }

  public function generateOrderSN(): void
  {
    $this->order_sn = 'od' . date('YmdHis') . rand(1111, 9999);
  }
}
