<?php

namespace App\Model;

use DateTime;
use Dbout\WpOrm\Orm\AbstractModel;
use Dbout\WpOrm\Orm\Builder;

/**
 * 菜单分类
 *
 * @property int $id 菜单分类ID
 * @property string $category_name 菜单分类名称
 * @property DateTime $create_time 创建时间
 * @property int $is_delete 是否删除
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
class MenuCategory extends AbstractModel
{
  public $timestamps = false;
  protected $table = 'res_menu_category';
  protected $primaryKey = 'id';
  protected $fillable = [
    'category_name',
    'create_time',
    'is_delete',
  ];

  protected $attributes = [
    'is_delete' => 0,
  ];

  protected $casts = [
    'is_delete' => 'integer',
    'create_time' => 'datetime',
  ];

  protected static function boot(): void
  {
    parent::boot();
    static::creating(function (MenuCategory $model) {
      $model->create_time = time();
    });
  }
}

