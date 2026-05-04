<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ArraySchema extends SchemaType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defaults['type'] = 'array';
        $this->defaults['items'] = [];
        $this->defaults['minItems'] = 0;
        $this->defaults['maxItems'] = 0;
        $this->defaults['enum'] = [];
        $this->defaults['enumNames'] = [];
        $this->defaults['uniqueItems'] = false;

        $resolver->setDefaults($this->defaults);
        parent::configureOptions($resolver);

        $resolver->setAllowedTypes('items', 'array');
        $resolver->setAllowedTypes('minItems', 'integer');
        $resolver->setAllowedTypes('maxItems', 'integer');
        $resolver->setAllowedTypes('uniqueItems', 'bool');
    }
}

