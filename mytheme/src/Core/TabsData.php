<?php

namespace App\Core;

use JsonSerializable;

final class TabsData implements JsonSerializable
{
    private string $badge = '';
    private array|string $content = [];
    private array $extendContent = [];
    private array $styles = [];
    private string $title = '';
    private string $extra = '';

    public function __construct(
        private string $link = "",
        private bool $isRouter = false,
    ) {
    }

    public function setTitle(string $title): TabsData
    {
        $this->title = $title;
        return $this;
    }

    public function setExtra(string $extra): TabsData
    {
        $this->extra = $extra;
        return $this;
    }

    public function setBadgeText(string $text): TabsData
    {
        $this->badge = $text;
        return $this;
    }

    public function setContent(
        string $title,
        int|string|float|null $value = null,
        int|string|float|null $count = null,
        array $valueEnums = [],
        ?string $stuffer = null,
        bool $isString = false,
    ): TabsData {
        if ($isString) {
            $this->content = $title;
        } else {
            $data = [
                'title' => $title,
            ];

            if ($value !== null) {
                $data['value'] = $value;
                $data['valueEnums'] = $valueEnums;
            } else {
                $data['count'] = $count;
                $data['stuffer'] = $stuffer;
            }

            $this->content[] = $data;
        }

        return $this;
    }

    public function setExtendContentConfig(
        string $meta_title
    ): TabsData {
        $this->extendContent['meta_title'] = $meta_title;

        return $this;
    }

    public function setExtendContent(
        string $title,
        int|string|float|null $value = null,
        int|string|float|null $count = null,
        array $valueEnums = [],
        ?string $stuffer = null,
        bool $isString = false,
    ): TabsData {
        if ($isString) {
            $this->extendContent['content'] = $title;
        } else {
            $data = [
                'title' => $title,
            ];

            if ($value !== null) {
                $data['value'] = $value;
                $data['valueEnums'] = $valueEnums;
            } else {
                $data['count'] = $count;
                $data['stuffer'] = $stuffer;
            }

            $this->extendContent['content'][] = $data;
        }

        return $this;
    }

    /**
     * 添加样式
     *
     * @param string $position  位置 root|content|title
     * @param StylesLayout $styles
     * @return TabsData
     */
    public function setStyle(string $position, StylesLayout $styles): TabsData
    {
        $this->styles[$position] = $styles;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'title' => $this->title,
            'isRouter' => $this->isRouter,
            'link' => $this->link,
            'content' => $this->content,
            'extra' => $this->extra,
            'style' => $this->styles,
            'extendContent' => $this->extendContent,
        ];
    }
}
