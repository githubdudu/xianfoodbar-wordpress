<?php

namespace App\Form;

use App\Entity\Account;
use App\Form\Type\NumberSchema;
use App\Form\Type\StringSchema;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class AccountType extends AbstractType
{
    /**
     * 
     */
    public RouterInterface $router;

    public function __construct(RouterInterface $router,)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', StringSchema::class, [
                'title' => '用户名',
                'description' => '用于登录的用户名',
                'minLength' => 5,
                'maxLength' => 20,
                'required' => true,
                'ui:column' => 2,
                'message' => [
                    'required' => '用户名是必填项目，不能为空',
                    'trim' => '用户名不能为空',
                    'maxLength' => '用户名长度不能超过20位',
                    'minLength' => '用户名长度不得少于5位',
                ],
            ])
            ->add('password', StringSchema::class, [
                'title' => '密码',
                'description' => '用于登录的密码',
                'minLength' => 5,
                'maxLength' => 20,
                'required' => true,
                'format' => 'password',
                'ui:column' => 2,
                'message' => [
                    'required' => '密码是必填项目，不能为空',
                    'trim' => '密码不能为空',
                    'maxLength' => '密码长度不能超过20位',
                    'minLength' => '密码长度不得少于5位',
                ],
            ])
            ->add('nickname', StringSchema::class, [
                'title' => '昵称',
                'description' => '显示的名称',
                'message' => [
                    'trim' => '昵称不能为空',
                ],
            ])
            ->add('avatar', StringSchema::class, [
                'title' => '头像',
                'description' => '显示的头像，可选',
                'format' => 'upload',
                'ui:options' => [
                    'action' => $this->router->generate('upload_file_api'),
                    'listType' => "picture-card",
                    'maxCount' => 1,
                ]
            ])
            ->add('sex', NumberSchema::class, [
                'title' => '性别',
                'description' => '可选',
                'format' => 'radio',
                'enum' => [0, 1, 2],
                'enumNames' => [
                    '保密', '男', '女'
                ],
            ])
            ->add('email', StringSchema::class, [
                'title' => '邮箱地址',
            ])
            ->add('phone', StringSchema::class, [
                'title' => '手机号码'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
