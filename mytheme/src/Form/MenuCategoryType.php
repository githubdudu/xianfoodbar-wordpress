<?php


namespace App\Form;

use App\Form\Type\NumberSchema;
use App\Form\Type\StringSchema;
use App\Model\MenuCategoryModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MenuCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('category_name', StringSchema::class, [
            'title' => '分类名称',
            'description' => '统计用的分类名称',
            'max' => 60,
            'required' => true,
            'placeholder' => '请输入分类名称',
            'rules' => [
                ['required' => true, "message" => '分类名称是必填项目，不能为空',],
            ],
            'props' => [
                'maxLength' => 60,
                'showCount' => true,
            ],
        ]);
    }
}
