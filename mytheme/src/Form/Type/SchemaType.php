<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemaType extends AbstractType
{
    public array $defaults = [
        'title' => '',
        'description' => '',
        'format' => '',
        'type' => '',
        'default' => '',
        'placeholder' => '',
        'bind' => '',
        'required' => false,
        'rules' => [],
        'props' => [],
        'disabled' => false,
        'readOnly' => false,
        'hidden' => false,
        'displayType' => '',
        'width' => '',
        'labelWidth' => '',
        'widget' => '',
        'min' => 0,
        'max' => 0,
        'items' => [],
        'message' => [],
        'auto_initialize' => false,
        'trim' => false,
        'mapped' => false,
        'by_reference' => false,
        'compound' => false,
        'legacy_error_messages' => false,
        'method' => '',
        'post_max_size_message' => '',
        'invalid_message' => '',
        'extra_fields_message' => '',
        'csrf_protection' => false,
        'csrf_field_name' => '',
        'csrf_message' => '',
        'empty_data' => null,
        'upload_max_size_message' => null,
        'csrf_token_manager' => null,
        'extra' => '',
    ];

    public array $required = [
        'title',
        'type'
    ];

    public array $options = [
        'title' => 'string',
        'description' => 'string',
        'format' => 'string',
        'type' => 'string',
        'default' => ['string', 'integer', 'boolean'],
        'placeholder' => ['string', 'array'],
        'bind' => ['string', 'array'],
        'required' => ['boolean', 'string'],
        'rules' => 'array',
        'props' => 'array',
        'disabled' => 'boolean',
        'readOnly' => 'boolean',
        'hidden' => ['boolean', 'string'],
        'displayType' => 'string',
        'width' => 'string',
        'labelWidth' => 'string',
        'widget' => 'string',
        'extra' => 'string',
        'items' => 'array',
        'message' => ['string', 'array']
    ];

    public function configureOptions(OptionsResolver $resolver)
    {
        foreach ($this->options as $key => $type) {
            $resolver->setAllowedTypes($key, $type);
        }
        $resolver->setAllowedTypes('upload_max_size_message', 'null');
        $resolver->setRequired('title');
    }
}
