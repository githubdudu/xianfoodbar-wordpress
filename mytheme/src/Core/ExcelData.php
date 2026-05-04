<?php

namespace App\Core;

use ReflectionClass;

final class ExcelData
{
    private ReflectionClass $refClass;

    public function __construct(
        private array $originData,
    ) {}

    /**
     * get Data
     */
    public function getData(string $name): mixed
    {
        return $this->originData[$name] ?? null;
    }
}
