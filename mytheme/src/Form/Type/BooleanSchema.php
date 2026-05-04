<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanSchema extends SchemaType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defaults['type'] = 'boolean';

        $resolver->setDefaults($this->defaults);
        parent::configureOptions($resolver);
    }
}
