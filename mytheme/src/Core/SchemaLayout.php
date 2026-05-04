<?php

namespace App\Core;

use JsonSerializable;

abstract class SchemaLayout implements JsonSerializable
{
    /**
     * JSON数组
     */
    public array $json = [];

    /**
     * 自定义规则
     */
    public const MessagePattren = 'pattern';
    /**
     * 必须
     */
    public const MessageRequired = 'required';
    /**
     * 最大长度
     */
    public const MessageMaxLength = 'maxLength';
    /**
     * 最小长度
     */
    public const MessageMinLength = 'minLength';
    /**
     * Number框最大长度
     */
    public const MessageMaximum = 'maximum';
    /**
     * Number框最小长度
     */
    public const MessageMinimum = 'minimum';
    /**
     * Email格式
     */
    public const MessageFormatEmail = 'format: email';
    /**
     * 图片格式
     */
    public const MessageFormatImage = 'format: image';
    /**
     * 链接格式
     */
    public const MessageFormatUrl = 'format: url';
    /**
     * 为空时
     */
    public const MessageTrim = 'trim';

    /**
     * 可控制 input、number、date、checkbox、radio、select、switch 对于组件的 disabled 属性
     */
    public const UiDisabled = 'disabled';
    /**
     * 用于控制本元素以及其子元素的标签宽度
     */
    public const UiLabelWidth = 'labelWidth';
    /**
     * 控制 input、number 组件中的 readOnly 属性
     */
    public const UiReadonly = 'readonly';
    /**
     * 可控制所有基础组件是否显示
     */
    public const UiHidden = 'hidden';
    /**
     * 添加组件 root 元素的 className
     */
    public const UiClassName = 'className';
    /**
     * 单个基础组件的长度
     */
    public const UiWidth = 'width';
    /**
     * 用于便捷控制一行排版几个元素
     */
    public const UiColumn = 'column';
    /**
     * 用于控制 label 和表单 input 是同行左右展示还是分两行展示
     */
    public const UiDisplayType = 'displayType';
    /**
     * 用于控制详情描述（description）是正常展示还是用一个 icon 替代
     */
    public const UiShowDescIcon = 'ui:showDescIcon';
    /**
     * 存放特定元素的特定配置
     */
    public const UiOptions = 'props';
    /**
     * 表单项对应的 UI Widget
     */
    public const UiWidget = 'widget';

    /**
     * 设置标题
     *
     * @param string $title
     * @return SchemaLayout
     */
    public function setTitle(string $title): SchemaLayout
    {
        $this->json['title'] = $title;
        return $this;
    }

    /**
     * 设置表单描述
     *
     * @param string $description
     * @return SchemaLayout
     */
    public function setDescription(string $description): SchemaLayout
    {
        if (empty($description)) {
            return $this;
        }
        $this->json['description'] = $description;
        return $this;
    }

    /**
     * 设置表单描述
     *
     * @param string $description
     * @return SchemaLayout
     */
    public function setPlaceholder(string $placeholder): SchemaLayout
    {
        if (empty($placeholder)) {
            return $this;
        }
        $this->json['placeholder'] = $placeholder;
        return $this;
    }

    /**
     * 设置显示的格式
     *
     * @param string $formatType
     * @return SchemaLayout
     */
    public function setFormate(string $formatType): SchemaLayout
    {
        if (empty($formatType)) {
            return $this;
        }
        $this->json['format'] = $formatType;
        return $this;
    }

    /**
     * 设置表单类型
     *
     * @param string $type
     * @return SchemaLayout
     */
    public function setType(string $type): SchemaLayout
    {
        $this->json['type'] = $type;
        return $this;
    }

    /**
     * 设置验证规则
     *
     * @param string $pattern
     * @return SchemaLayout
     */
    public function setRules(array $pattern): SchemaLayout
    {
        if (empty($pattern)) {
            return $this;
        }
        $this->json['rules'] = $pattern;
        return $this;
    }

    // /**
    //  * 设置指定规则验证失败时的提示消息
    //  *
    //  * @param string $messageType
    //  * @param string $message
    //  * @return SchemaLayout
    //  */
    // public function setMessage(string $messageType, string $message): SchemaLayout {
    //     $this->json['message'][$messageType] = $message;
    //     return $this;
    // }

    /**
     * 设置显示方式，列或行
     *
     * @param string $type row or col
     * @return SchemaLayout
     */
    public function setDisplayType(string $type): SchemaLayout
    {
        if (empty($type)) {
            return $this;
        }
        $this->json['displayType'] = $type;
        return $this;
    }

    public function setProps(array $props): SchemaLayout
    {
        if (empty($props)) {
            return $this;
        }
        $this->json['props'] = $props;
        return $this;
    }

    public function setLabelWidth(string $labelWidth): SchemaLayout
    {
        if (empty($labelWidth)) {
            return $this;
        }
        $this->json['labelWidth'] = $labelWidth;
        return $this;
    }

    public function setWidget(string $widget): SchemaLayout
    {
        if (empty($widget)) {
            return $this;
        }
        $this->json['widget'] = $widget;
        return $this;
    }

    public function isRequired(): SchemaLayout
    {
        $this->json['required'] = true;
        return $this;
    }

    public function isHidden(): SchemaLayout
    {
        $this->json['hidden'] = true;
        return $this;
    }

    public function isReadonly(): SchemaLayout
    {
        $this->json['readonly'] = true;
        return $this;
    }

    public function isDisable(): SchemaLayout
    {
        $this->json['disabled'] = true;
        return $this;
    }

    /**
     * 设置自定义UI规则
     *
     * @param string $uiName
     * @param mixed $value
     * @return SchemaLayout
     */
    public function setUiSchema(string $uiName, $value = null): SchemaLayout
    {
        $this->json[$uiName] = $value;
        return $this;
    }

    /**
     * 设置最小长度
     *
     * @param integer $minLength
     * @return SchemaLayout
     */
    public function setMinLength(int $minLength): SchemaLayout
    {
        if (empty($minLength)) {
            return $this;
        }
        $this->json['min'] = $minLength;
        return $this;
    }

    /**
     * 设置最大长度
     *
     * @param integer $minLength
     * @return SchemaLayout
     */
    public function setMaxLength(int $maxLength): SchemaLayout
    {
        if (empty($maxLength)) {
            return $this;
        }
        $this->json['max'] = $maxLength;
        return $this;
    }

    /**
     * 设置选项值
     *
     * @param integer $minLength
     * @return SchemaLayout
     */
    public function setEnum(array $enum): SchemaLayout
    {
        if (empty($enum)) {
            return $this;
        }
        $this->json['enum'] = $enum;
        return $this;
    }

    /**
     * 设置选项的文案
     *
     * @param array $enumNames
     * @return SchemaLayout
     */
    public function setEnumNames(array $enumNames): SchemaLayout
    {
        if (empty($enumNames)) {
            return $this;
        }
        $this->json['enumNames'] = $enumNames;
        return $this;
    }

    public function __toString()
    {
        return json_encode($this->json);
    }

    public function jsonSerialize(): mixed
    {
        return $this->json;
    }
}
