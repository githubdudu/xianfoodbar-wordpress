<?php

namespace App\Form;

use App\Form\Type\BooleanSchema;
use App\Form\Type\NumberSchema;
use App\Form\Type\StringSchema;
use App\Model\Desk;
use App\Model\MenuCategoryModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;

class SettingType extends AbstractType
{
    /**
     *
     */
    public RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tempMenu = [];
        $tempMenuValues = [];
        $menuCate = Desk::all();

        foreach ($menuCate as $menu) {
            $tempMenu[] = $menu->did;
            $tempMenuValues[] = $menu->desk_name;
        }
        $builder->add('desk_audio', StringSchema::class, [
            'title' => '餐厅下单提示音',
            'description' => '餐厅下单提示音',
            'format' => 'upload',
            'required' => true,
            'placeholder' => '请输入菜单名称',
            'rules' => [
                ['required' => true, "message" => '餐厅下单提示音是必填项目，不能为空',],
            ],
            'props' => [
                'action' => $this->router->generate('wp_upload_file_api'),
                'maxCount' => 1,
            ],
        ])->add('takeway_type1_audio', StringSchema::class, [
            'title' => '网站外卖下单提示音',
            'description' => '网站外卖下单提示音',
            'format' => 'upload',
            'required' => true,
            'placeholder' => '请输入网站外卖下单提示音',
            'rules' => [
                ['required' => true, "message" => '网站外卖下单提示音是必填项目，不能为空',],
            ],
            'props' => [
                'action' => $this->router->generate('wp_upload_file_api'),
                'maxCount' => 1,
            ],
        ])->add('takeway_type2_audio', StringSchema::class, [
            'title' => '外卖下单提示音',
            'description' => '外卖下单提示音',
            'format' => 'upload',
            'required' => true,
            'placeholder' => '请输入外卖下单提示音',
            'rules' => [
                ['required' => true, "message" => '外卖下单提示音是必填项目，不能为空',],
            ],
            'props' => [
                'action' => $this->router->generate('wp_upload_file_api'),
                'maxCount' => 1,
            ],
        ])->add('takeway_type3_audio', StringSchema::class, [
            'title' => '加菜提示音',
            'description' => '加菜提示音',
            'format' => 'upload',
            'required' => true,
            'placeholder' => '请输入加菜提示音',
            'rules' => [
                ['required' => true, "message" => '加菜提示音是必填项目，不能为空',],
            ],
            'props' => [
                'action' => $this->router->generate('wp_upload_file_api'),
                'maxCount' => 1,
            ],
        ])->add('site_takeway_did', NumberSchema::class, [
            'title' => '网站外卖下单桌号',
            'description' => '网站外卖下单桌号',
            'required' => true,
            'placeholder' => '请输入网站外卖下单桌号ID',
            'enum' => $tempMenu,
            'enumNames' => $tempMenuValues,
            'rules' => [
                ['required' => true, "message" => '网站外卖下单桌号是必填项目，不能为空',],
            ],
            'props' => [],
        ])->add('active_order', StringSchema::class, [
            'title' => '有备注的提示颜色',
            'description' => '有备注的提示颜色',
            'placeholder' => '请输入有备注的提示颜色',
            'format' => 'color',
            'default' => '#000',
            'rules' => [],
            'props' => [],
        ])->add('new_active_order', StringSchema::class, [
            'title' => '新订单的提示颜色',
            'description' => '新订单的提示颜色',
            'placeholder' => '请输入新订单的提示颜色',
            'format' => 'color',
            'default' => '#000',
            'rules' => [],
            'props' => [],
        ])->add('add_active_order', StringSchema::class, [
            'title' => '加菜的提示颜色',
            'description' => '加菜的提示颜色',
            'placeholder' => '请输入加菜的提示颜色',
            'format' => 'color',
            'default' => '#000',
            'rules' => [],
            'props' => [],
        ])->add('big_fonts', NumberSchema::class, [
            'title' => '后厨显示的字体大小',
            'description' => '后厨显示的字体大小',
            'placeholder' => '请输入后厨显示的字体大小',
            'default' => 34,
            'rules' => [],
            'props' => [],
        ])->add('can_qrcode', BooleanSchema::class, [
            'title' => '是否开启扫码',
            'description' => '允许前端扫码',
            'placeholder' => '请选择开启或关闭',
            'default' => true,
            'rules' => [],
            'props' => [],
        ])->add('cook_intval', NumberSchema::class, [
            'title' => '后厨刷新时间',
            'description' => '后厨刷新时间',
            'placeholder' => '后厨刷新时间',
            'default' => 5,
        ]);
    }
}
