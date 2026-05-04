<?php

namespace App\Core\Ui\Attribute;

use Attribute;

#[Attribute()]
class Form
{

    public function __construct(
        private string $type,
        private string $title,
        private string $format = '',
        private string $description = '',
        private string $placeholder = '',
        private bool $required = false,
        private bool $readonly = false,
        private bool $hidden = false,
        private bool $disabled = false,
        private int $min = 0,
        private int $max = 0,
        private string $displayType = '',
        private string $widget = '',
        private string $labelWidth = '',
        private array $enumNames = [],
        private array $enum = [],
        private array $rules = [],
        private array $props = [],
        private array $options = [],
    ) {
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function getRequired()
    {
        return $this->required;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getEnum()
    {
        return $this->enum;
    }

    public function getEnumNames()
    {
        return $this->enumNames;
    }

    public function getRules() {
        return $this->rules;
    }

    public function getProps() {
        return $this->props;
    }
    
    public function getHidden() {
        return $this->hidden;
    }

    public function getDisabled() {
        return $this->disabled;
    }

    public function getDisplayType() {
        return $this->displayType;
    }

    public function getFormat() {
        return $this->format;
    }

    public function getReadonly() {
        return $this->readonly;
    }

    public function getWidgets() {
        return $this->widget;
    }

    public function getLabelWidth() {
        return $this->labelWidth;
    }

    public function getMin() {
        return $this->min;
    }

    public function getMax() {
        return $this->max;
    }
}
