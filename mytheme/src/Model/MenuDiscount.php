<?php

namespace App\Model;

use Dbout\WpOrm\Orm\AbstractModel;
use Dbout\WpOrm\Orm\Builder;
use DateTime;

/**
 * 菜品折扣
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $is_delete
 * @property int $discount_amount
 * @property int $discount_percent
 * @property int $discount_type
 * @property DateTime $discount_start_time
 * @property DateTime $discount_end_time
 * @property array $discount_menus
 * @property DateTime $discout_date
 * @property int $status
 * @property DateTime $create_time
 * @property DateTime $update_time
 *
 * @method static Builder|MenuDiscount where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|MenuDiscount where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder|MenuDiscount whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|MenuDiscount whereOr($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|MenuDiscount whereHas($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|MenuDiscount  whereNull($columns, $boolean = 'and', $not = false)
 * @method static Builder|MenuDiscount whereNotNull($columns, $boolean = 'and')
 * @method static Builder|MenuDiscount whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method static Builder|MenuDiscount whereBetween($column, $values, $boolean = 'and', $not = false)
 * @method static Builder|MenuDiscount find($id)
 * @method static Builder|MenuDiscount orderBy($column, $direction = 'asc')
 * @method static Builder|MenuDiscount query()
 * @method static Builder|MenuDiscount select($columns = ['*'])
 */
class MenuDiscount extends AbstractModel
{
    protected $table = 'res_menu_discount';
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'description',
        'is_delete',
        'discount_amount',
        'discount_percent',
        'discount_type',
        'discount_start_time',
        'discount_end_time',
        'discount_menus',
        'discout_date',
        'status',
        'create_time',
        'update_time',
    ];

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $attributes = [
        'is_delete' => 0,
        'discount_type' => 0,
        'discount_menus' => [],
        'discount_start_time' => null,
        'discount_end_time' => null,
        'discout_date' => null,
        'status' => 0,
    ];

    protected $casts = [
        'discount_menus' => 'array',
        'is_delete' => 'integer',
        'discount_type' => 'integer',
        'status' => 'integer',
    ];

    public function getDiscountPrice(Menu $menu): float
    {
        $allDiscount = $this
            ->query()
            ->where('is_delete', 0)
            ->where('discount_start_time', '<=', date('Y-m-d'))
            ->where('discount_end_time', '>=', date('Y-m-d'))
            ->get();
        if ($allDiscount) {
            foreach ($allDiscount as $discount) {
                if (in_array($menu->id, $discount->discount_menus)) {
                    if ($discount->discount_type == 1) {
                        $percentage = $discount->discount_percent / 100;
                        return $menu->menu_price - ($menu->menu_price * $percentage);
                    } else {
                        return $menu->menu_price - $discount->discount_amount;
                    }
                }
            }
        }
        return $menu->menu_price;
    }

    public function getDiscountPriceByIdPrice(int $id, float $price): float
    {
        $allDiscount = $this
            ->query()
            ->where('is_delete', 0)
            ->where('discount_start_time', '<=', date('Y-m-d'))
            ->where('discount_end_time', '>=', date('Y-m-d'))
            ->get();
        if ($allDiscount) {
            foreach ($allDiscount as $discount) {
                if (in_array($id, $discount->discount_menus)) {
                    if ($discount->discount_type == 1) {
                        $percentage = $discount->discount_percent / 100;
                        return $price - ($price * $percentage);
                    } else {
                        return $price - $discount->discount_amount;
                    }
                }
            }
        }
        return $price;
    }
}
