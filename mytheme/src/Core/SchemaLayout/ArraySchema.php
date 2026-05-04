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
class ArraySchema extends SchemaLayout {

    /**
     * Undocumented function
     * @return ArraySchema
     */
    public function __construct()
    {
        $this->setType('arary');
    }
    /**
     * 描述 Array 中每个 item 的结构、类型
     *
     * @param SchemaLayout $items
     * @return ArraySchema
     */
    public function setItems(SchemaLayout $items): ArraySchema {
        $this->json['items'] = $items;
        return $this;
    }

    /**
     * 最少数组项为几项
     *
     * @param integer $minItems
     * @return ArraySchema
     */
    public function setMinItems(int $minItems): ArraySchema {
        $this->json['minItems'] = $minItems;
        return $this;
    }

    /**
     * 最多数组项为几项
     *
     * @param integer $maxItems
     * @return ArraySchema
     */
    public function setMaxItems(int $maxItems): ArraySchema {
        $this->json['maxItems'] = $maxItems;
        return $this;
    }

    /**
     * 用于判断数组的元素是否有重复
     *
     * @param boolean $uniqueItems
     * @return ArraySchema
     */
    public function setUniqueItems(bool $uniqueItems): ArraySchema {
        $this->json['uniqueItems'] = $uniqueItems;
        return $this;
    }
}