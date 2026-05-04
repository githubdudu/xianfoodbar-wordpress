<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class StringTextareaType extends SchemaType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->defaults['type'] = 'string';
        $this->defaults['format'] = 'textarea';
        $this->defaults['min'] = 0;
        $this->defaults['max'] = 0;
        $this->defaults['enum'] = [];
        $this->defaults['enumNames'] = [];

        $resolver->setDefaults($this->defaults);

        parent::configureOptions($resolver);

        $resolver->setAllowedTypes('min', 'integer');
        $resolver->setAllowedTypes('max', 'integer');
        $resolver->setAllowedTypes('enum', 'array');
        $resolver->setAllowedTypes('enumNames', 'array');
    }
}
