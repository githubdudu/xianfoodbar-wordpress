<?php

namespace App\Model;

use Dbout\WpOrm\Orm\AbstractModel;
use Dbout\WpOrm\Orm\Builder;

/**
 * 桌子
 *
 * @property int $id 桌子ID
 * @property int $did 桌子ID
 * @property int $key 桌子ID
 * @property string $desk_name 桌号
 * @property string $desk_subname 桌号
 * @property int $desk_status 使用状态
 * @property string $add_time 添加时间
 * @property int $use_status 使用状态
 * @property string $update_time 更新时间
 * @property string $menu_guid 菜单GUID
 * @property int $is_takeway 是否外卖
 *
 * @method static Builder|Desk where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|Desk whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Desk whereOr($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Desk whereHas($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Desk whereNull($columns, $boolean = 'and', $not = false)
 * @method static Builder|Desk whereNotNull($columns, $boolean = 'and')
 * @method static Builder|Desk whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method static Builder|Desk whereBetween($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|Desk find($id)
 * @method static Builder|Desk orderBy($column, $direction = 'asc')
 * @method static Builder|Desk query()
 * @method static Builder|Desk select($columns = ['*'])
 */
class Desk extends AbstractModel
{
  protected $table = 'res_desk';
  protected $primaryKey = 'id';

  public $timestamps = false;
  const UPDATED_AT = '';
  const CREATED_AT = 'add_time';

  protected $attributes = [
    'is_takeway' => 0,
    'desk_status' => 0,
    'use_status' => 0,
  ];
  protected $fillable = [
    'desk_name',
    'desk_subname',
    'desk_status',
    'use_status',
    'menu_guid',
    'is_takeway',
  ];

  protected $appends = [
    'did',
    'key',
    'qr_url'
  ];

  protected $casts = [
    'desk_status' => 'integer',
    'use_status' => 'integer',
    'is_takeway' => 'integer',
    'add_time' => 'datetime',
  ];

  public function getQrUrlAttribute(): string
  {

    return get_site_url() . '/?id=' . $this->id;
  }

  protected function initializeDesk(): void
  {
    $this->attributes['add_time'] = new \DateTimeImmutable();
  }

  public function getKeyAttribute(): int
  {
    return $this->id;
  }

  public function getDidAttribute(): int
  {
    return $this->id;
  }
}
