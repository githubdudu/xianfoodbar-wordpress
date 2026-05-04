<?php

namespace App\Core\SchemaLayout;

use App\Core\SchemaLayout;

/**
 * @method HtmlSchema setTitle(string $title)
 * @method HtmlSchema setDescription(string $description)
 * @method HtmlSchema setFormate(string $formatType)
 * @method HtmlSchema setType(string $type)
 * @method HtmlSchema setPattern(string $pattern)
 * @method HtmlSchema setMessage(string $messageType, string $message)
 * @method HtmlSchema setDisplayType(string $type)
 * @method HtmlSchema setUiSchema(string $uiName, $value = null)
 */
class HtmlSchema extends SchemaLayout {

    /**
     * __construct
     * @return HtmlSchema
     */
    public function __construct()
    {
        $this->setType('html');
    }
}