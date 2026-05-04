<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberSchema extends SchemaType {

    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defaults['type'] = 'number';
        $this->defaults['min'] = 0;
        $this->defaults['max'] = 0;
        $this->defaults['step'] = 0;
        $this->defaults['enum'] = [];
        $this->defaults['enumNames'] = [];

        $resolver->setDefaults($this->defaults);

        parent::configureOptions($resolver);

        $resolver->setAllowedTypes('min', 'integer');
        $resolver->setAllowedTypes('step', ['integer', 'float', 'string']);
        $resolver->setAllowedTypes('max', 'integer');
        $resolver->setAllowedTypes('enum', 'array');
        $resolver->setAllowedTypes('enumNames', 'array');
        
    }
}