<?php


namespace App\Form;

use App\Form\Type\NumberSchema;
use App\Form\Type\StringSchema;
use App\Model\MenuCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tempMenu = [];
        $tempMenuValues = [];
        $menuCate = MenuCategory::all();
        $extra = [];

        foreach ($menuCate as $menu) {
            $tempMenu[] = $menu->id;
            $tempMenuValues[] = $menu->category_name;
            $extra[] = $menu->category_name . ":" . $menu->id;
        }

        $builder->add('menu_name', StringSchema::class, [
            'title' => '菜单名称',
            'description' => '显示的菜单名称',
            'max' => 60,
            'required' => true,
            'placeholder' => '请输入菜单名称',
            'rules' => [
                ['required' => true, "message" => '菜单名称是必填项目，不能为空',],
                ['max' => 60, "message" => '菜单名称长度不能超过60位',],
            ],
            'props' => [
                'maxLength' => 60,
                'showCount' => true,
            ],
        ])->add('menu_subname', StringSchema::class, [
            'title' => '菜单副标题',
            'description' => '菜单副标题',
            'max' => 255,
            'required' => true,
            'placeholder' => '请输入菜单副标题',
            'rules' => [
                ['required' => true, "message" => '菜单副标题是必填项目，不能为空',],
                ['max' => 255, "message" => '菜单副标题长度不能超过255位',],
            ],
            'props' => [
                'maxLength' => 255,
                'showCount' => true,
            ],
        ])
            ->add('menu_num', StringSchema::class, [
                'title' => '菜单编号',
                'placeholder' => '请输入菜品编号',
                'description' => '用于点单的菜品编号',
                'required' => true,
                'rules' => [
                    ['required' => true, "message" => '菜单编号是必填项目，不能为空',],
                ],
                'props' => [],
            ])->add('menu_price', StringSchema::class, [
                'title' => '菜品价格',
                'placeholder' => '请输入菜品价格',
                'description' => '用于点单的菜品价格',
                'default' => '0',
                'required' => true,
                'rules' => [
                    ['required' => true, "message" => '菜品价格是必填项目，不能为空',],
                ],
                'props' => [
                    'step' => 0.01,
                    'stringMode' => true,
                ],
            ])->add('menu_note', StringSchema::class, [
                'title' => '备注',
                'description' => '备注',
                'format' => 'textarea',
                'placeholder' => '请输入备注',
                'rules' => [],
                'props' => [
                    'showCount' => true,
                ],
            ])->add('menu_count', NumberSchema::class, [
                'title' => '菜品数量',
                'placeholder' => '请输入菜品数量',
                'description' => '显示的菜品数量',
                'props' => [],
            ])->add('menu_sales', NumberSchema::class, [
                'title' => '菜品销量',
                'placeholder' => '请输入菜品销量',
                'description' => '用于点单的菜品销量',
                'default' => 0,
                'props' => [],
            ])->add('menu_category', NumberSchema::class, [
                'title' => '菜品分类',
                'placeholder' => '请输入菜品分类',
                'description' => '显示的菜品分类',
                'default' => 0,
                'format' => 'radio',
                'extra' => implode('<br>', $extra),
                'props' => [],
            ])
            ->add('out_site_id', NumberSchema::class, [
                'title' => '外部菜品的ID',
                'placeholder' => '请输入外部菜品的ID',
                'description' => '用于外卖的外部菜品的ID',
                'props' => [],
            ]);
    }
}
