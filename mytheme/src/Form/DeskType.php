<?php


namespace App\Form;

use App\Form\Type\NumberSchema;
use App\Form\Type\StringSchema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DeskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('desk_name', StringSchema::class, [
            'title' => '桌位名称',
            'description' => '显示的名称',
            'max' => 20,
            'required' => true,
            'placeholder' => '请输入桌位名称',
            'rules' => [
                ['required' => true, "message" => '桌位名称是必填项目，不能为空',],
                ['max' => 20, "message" => '用户名长度不能超过20位',],
            ],
            'props' => [
                'maxLength' => 20,
                'showCount' => true,
            ],
        ])->add('desk_subname', StringSchema::class, [
            'title' => '桌位副标题',
            'description' => '显示的桌位副标题（英语名称）',
            'max' => 255,
            'required' => true,
            'placeholder' => '请输入桌位副标题',
            'rules' => [
                ['required' => true, "message" => '桌位副标题是必填项目，不能为空',],
                ['max' => 255, "message" => '用户名长度不能超过255位',],
            ],
            'props' => [
                'maxLength' => 255,
                'showCount' => true,
            ],
        ])
        ->add('menu_guid', StringSchema::class, [
            'title' => '桌位编号',
            'placeholder' => '请输入桌位编号',
            'description' => '后厨显示的编号',
            'max' => 20,
            'required' => true,
            'rules' => [
                ['required' => true, "message" => '桌位编号是必填项目，不能为空',],
                ['max' => 20, "message" => '桌位编号长度不能超过20位',],
            ],
            'props' => [
                'maxLength' => 20
            ],
        ])->add('is_takeway', NumberSchema::class, [
            'title' => '是否为外卖',
            'placeholder' => '是否为外卖',
            'description' => '是否为外卖',
            'required' => true,
            'format' => 'radio',
            'enum' => [1, 0],
            'enumNames' => ['是', '否'],
            'rules' => [
                ['required' => true, "message" => '此项是必填项目，不能为空',],
            ],
            'props' => [
            ],
        ]);
    }
}
