<?php

namespace App\Core;

class CheckType {

    static private bool $isHtmlType = false;
    static private mixed $arguments = null;

    public static function isHtmlType(): bool {
        return self::$isHtmlType;
    }

    public static function setHtmlType(bool $type): void {
        self::$isHtmlType = $type;
    }

    public static function setHtmlArguments(mixed $arguments) {
        self::$arguments = $arguments;
    }

    public static function getArguments(): mixed {
        return self::$arguments;
    }
}