<?php

namespace App\Core;

class DateTime extends \DateTime implements \Stringable
{
    public function __toString()
    {
        return $this->format('Y-m-d H:i:s');
    }
}