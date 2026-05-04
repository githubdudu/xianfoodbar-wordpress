<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class RangeSchema extends SchemaType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defaults['type'] = 'range';
        $this->defaults['format'] = 'dateTime';

        $resolver->setDefaults($this->defaults);

        parent::configureOptions($resolver);
    }
}

