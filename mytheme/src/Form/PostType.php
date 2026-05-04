<?php

namespace App\Form;

use App\Entity\Post;
use Laminas\Db\Sql\Ddl\Column\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('post_title', TextType::class)
            ->add('post_author', TextType::class)
            ->add('post_name', TextType::class)
            ->add('post_type', TextType::class)
            ->add('cat_id', ChoiceType::class)
            ->add('post_status', ChoiceType::class)
            ->add('post_date', DateTimeType::class)
            ->add('post_update', DateTimeType::class)
            ->add('post_thumb', FileType::class)
            ->add('post_desc', TextareaType::class)
            ->add('post_password', PasswordType::class)
            ->add('post_link', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
