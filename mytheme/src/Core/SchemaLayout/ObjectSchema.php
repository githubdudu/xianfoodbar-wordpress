<?php

namespace App\Core\SchemaLayout;

use App\Core\SchemaLayout;


class ObjectSchema extends SchemaLayout {

    /**
     * __construct
     * @return ObjectSchema
     */
    public function __construct()
    {
        $this->setType('object');
    }
    /**
     * 设置是否为必须
     *
     * @param string $required
     * @return ObjectSchema
     */
    public function setRequired(string $required): ObjectSchema {
        $this->json['required'][] = $required;
        return $this;
    }

    /**
     * 子结构
     *
     * @param string $name 表单的NAME
     * @param SchemaLayout|array $properties 表单内容
     * @return ObjectSchema
     */
    public function setProperties(string $name, ?SchemaLayout $properties): ObjectSchema {
        $this->json['properties'][$name] = $properties;
        return $this;
    }
}