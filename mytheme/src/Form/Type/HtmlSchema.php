<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class HtmlSchema extends SchemaType {

    public function configureOptions(OptionsResolver $resolver)
    {
        $this->defaults['type'] = 'html';

        $resolver->setDefaults($this->defaults);
        parent::configureOptions($resolver);
        
    }
}
