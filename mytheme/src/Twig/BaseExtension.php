<?php

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BaseExtension extends AbstractExtension
{
    

    public function __construct(
        
    ) {

    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('wp_head', 'wp_head'),
        ];
    }    
}
