<?php

namespace App\Core\Ui\Attribute;

use Attribute;

#[Attribute()]
class Table
{

    public function __construct(
        private string $title = '',
        private string $subTitle = '',
        private string $description = '',
        private array $buttonList = [],
        private array $toolButtonList = [],
        private array $alertButtonList = [],
        private array $apiList = [],
        private bool $showSelect = false,
    ) {
    }

    private function getTitle()
    {
        return $this->title;
    }

    private function getSubTitle()
    {
        return $this->subTitle;
    }

    private function getDescription()
    {
        return $this->description;
    }

    private function getButtonList()
    {
        return $this->buttonList;
    }

    private function getToolButtonList()
    {
        return $this->toolButtonList;
    }

    private function getAlertButtonList()
    {
        return $this->alertButtonList;
    }

    private function getApiList()
    {
        return $this->apiList;
    }

    private function getShowSelect()
    {
        return $this->showSelect;
    }
}
