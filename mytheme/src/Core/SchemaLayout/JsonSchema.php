<?php

namespace App\Core\SchemaLayout;

use App\Core\SchemaLayout;

/**
 * @method JsonSchema setTitle(string $title)
 * @method JsonSchema setDescription(string $description)
 * @method JsonSchema setFormate(string $formatType)
 * @method JsonSchema setType(string $type)
 * @method JsonSchema setPattern(string $pattern)
 * @method JsonSchema setMessage(string $messageType, string $message)
 * @method JsonSchema setDisplayType(string $type)
 * @method JsonSchema setUiSchema(string $uiName, $value = null)
 */
class JsonSchema extends SchemaLayout {

    /**
     * __construct
     * @return SchemaLayout
     */
    public function __construct(array $json = [])
    {
        $this->json = array_merge($this->json, $json);
    }
}