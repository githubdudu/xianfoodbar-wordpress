<?php

namespace App\Core\SchemaLayout;

use App\Core\SchemaLayout;

/**
 * @method ArraySchema setTitle(string $title)
 * @method ArraySchema setDescription(string $description)
 * @method ArraySchema setFormate(string $formatType)
 * @method ArraySchema setType(string $type)
 * @method ArraySchema setPattern(string $pattern)
 * @method ArraySchema setMessage(string $messageType, string $message)
 * @method ArraySchema setDisplayType(string $type)
 * @method ArraySchema setUiSchema(string $uiName, $value = null)
 */
class NumberSchema extends SchemaLayout {

    /**
     * __construct
     * @return NumberSchema
     */
    public function __construct()
    {
        $this->setType('number');
    }

    /**
     * 设置步进区间
     *
     * @param integer $step
     * @return NumberSchema
     */
    public function setStep(int $step): NumberSchema {
        $this->json['step'] = $step;
        return $this;
    }

}