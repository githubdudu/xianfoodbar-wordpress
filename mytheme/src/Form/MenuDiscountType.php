<?php

namespace App\Form;

use App\Form\Type\ArraySchema;
use App\Form\Type\NumberSchema;
use App\Form\Type\RangeSchema;
use App\Form\Type\StringSchema;
use App\Form\Type\StringTextareaType;
use App\Model\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MenuDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allMenu = Menu::query()->pluck('menu_name', 'menu_num')->mapWithKeys(function ($item, $key) {
            return [strval($key) => $item];
        })->toArray();
        $builder->add('title', StringSchema::class, [
            'title' => '折扣项目名称',
            'description' => '统计用的折扣项目名称',
            'max' => 100,
            'required' => true,
            'placeholder' => '请输入折扣项目名称',
            'rules' => [
                [
                    'required' => true,
                    'message' => '折扣项目名称是必填项目，不能为空',
                ],
            ],
            'props' => [
                'maxLength' => 100,
                'showCount' => true,
            ],
        ]);

        $builder->add('description', StringTextareaType::class, [
            'title' => '折扣项目描述',
            'description' => '统计用的折扣项目描述',
            'max' => 500,
            'required' => false,
            'placeholder' => '请输入折扣项目描述',
            'rules' => [],
            'props' => [
                'maxLength' => 500,
                'showCount' => true,
            ],
        ]);

        $builder->add('discount_type', StringSchema::class, [
            'title' => '折扣类型',
            'description' => '统计用的折扣类型',
            'default' => '0',
            'required' => true,
            'widget' => 'radio',
            'enum' => [
                '1',
                '0'
            ],
            'enumNames' => [
                '百分比',
                '金额',
            ]
        ]);

        $builder->add('discount_percent', StringSchema::class, [
            'title' => '折扣百分比',
            'description' => '统计用的折扣百分比',
            'max' => 100,
            'min' => 0,
            'hidden' => '{{formData.discount_type != 1}}',
            'required' => '{{ formData.discount_type == 1 ? true : false }}',
            'placeholder' => '请输入折扣百分比',
            'rules' => [],
            'props' => [],
        ]);

        $builder->add('discount_amount', StringSchema::class, [
            'title' => '折扣金额',
            'description' => '统计用的折扣金额',
            'hidden' => '{{  formData.discount_type != 0 }}',
            'required' => '{{ formData.discount_type == 0 ? true : false }}',
            'placeholder' => '请输入折扣金额',
            'rules' => [],
            'props' => [],
        ]);

        $builder->add('time_range', RangeSchema::class, [
            'title' => '打折时间',
            'description' => '统计用的打折时间',
        ]);

        $builder->add('meuns', ArraySchema::class, [
            'title' => '折扣菜品项目',
            'description' => '统计用的折扣菜品项目',
            'items' => [
                'type' => 'string',
            ],
            'props' => [
                'mode' => 'multiple',
                'showSearch' => true,
                'showArrow' => true,
            ],
            'widget' => 'multiSelect',
            'enum' => array_keys($allMenu),
            'enumNames' => array_values($allMenu),
            'required' => true,
        ]);
    }
}
