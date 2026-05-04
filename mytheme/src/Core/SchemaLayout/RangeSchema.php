<?php

namespace App\Core\SchemaLayout;

use App\Core\SchemaLayout;

/**
 * @method RangeSchema setTitle(string $title)
 * @method RangeSchema setDescription(string $description)
 * @method RangeSchema setFormate(string $formatType)
 * @method RangeSchema setType(string $type)
 * @method RangeSchema setPattern(string $pattern)
 * @method RangeSchema setMessage(string $messageType, string $message)
 * @method RangeSchema setDisplayType(string $type)
 * @method RangeSchema setUiSchema(string $uiName, $value = null)
 */
class RangeSchema extends SchemaLayout {

    /**
     * __construct
     * @return RangeSchema
     */
    public function __construct()
    {
        $this->setType('range');
        $this->setFormate('dateTime');
    }
}