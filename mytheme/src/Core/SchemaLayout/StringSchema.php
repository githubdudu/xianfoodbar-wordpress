<?php

namespace App\Core\SchemaLayout;

use App\Core\SchemaLayout;

/**
 * @method StringSchema setTitle(string $title)
 * @method StringSchema setDescription(string $description)
 * @method StringSchema setFormate(string $formatType)
 * @method StringSchema setType(string $type)
 * @method StringSchema setPattern(string $pattern)
 * @method StringSchema setMessage(string $messageType, string $message)
 * @method StringSchema setDisplayType(string $type)
 * @method StringSchema setUiSchema(string $uiName, $value = null)
 */
class StringSchema extends SchemaLayout {

    /**
     * __construct
     * @return StringSchema
     */
    public function __construct()
    {
        $this->setType('string');
    }
    
}