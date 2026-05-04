<?php

namespace App\Core\Ui\Attribute;

use Attribute;

#[Attribute()]
class TableCol {

    public function __construct(
        private string $title = '',
        private string|array|null $render = null,
        private array $option = [],
    ) {
    }

    public function getTitle() {
        return $this->title;
    }

    public function getRender() {
        return $this->render;
    }

    public function getOption() {
        return $this->option;
    }
}