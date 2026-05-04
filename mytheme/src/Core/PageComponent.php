<?php

namespace App\Core;

use JsonSerializable;

class PageComponent implements JsonSerializable
{
    public function __construct(
        public string $name,
        public string $componentName,
        public ?array $prop = null,
        public ?array $event = null,
        public ?array $children = null,
    ) {
    }

    /**
     * 设置属性
     *
     * @param string $name 属性名
     * @param mixed $data 数据
     * @return PageComponent
     */
    public function setProperty(string $name, mixed $data): PageComponent
    {
        $this->prop[$name] = $data;
        return $this;
    }

    /**
     * 组件事件
     *
     * @param string $event_name 事件名
     * @param string $data 事件代码
     * @return PageComponent
     */
    public function setEvent(string $event_name, string $data): PageComponent
    {
        $this->event[$event_name] = $data;
        return $this;
    }

    /**
     * 添加子组件
     *
     * @param PageComponent $child 子组件
     * @return PageComponent
     */
    public function appendChild(PageComponent $child): PageComponent
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * 删除指定的子组件
     *
     * @param PageComponent|string $search 组件或组件名
     * @param integer $searchIndex 从此开始删除
     * @param integer $searchCount 删除的数量
     * @return PageComponent
     */
    public function removeChild(PageComponent|string $search, int $searchIndex = 0, int $searchCount = 0): PageComponent
    {
        $countIndex = 0;
        $countSearch = 0;
        foreach ($this->children as $key => $child) {
            if ($child === $search || $child->componentName === $search) {
                if ($searchIndex === 0 || $countIndex > $searchIndex) {
                    unset($this->children[$key]);
                }
                if ($searchCount > 0 && $countSearch <= $searchCount) {
                    break;
                }
                $countSearch ++;
                $countIndex ++;
            }
        }
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        if ($this->event !== null) {
            foreach ($this->event as $eventName => $event) {
                $this->prop['on' . ucfirst($eventName)] = sprintf('{{ %s }}', $event);
            }
        }

        return [
            'name' => $this->name,
            'component' => $this->componentName,
            'prop' => $this->prop,
            'children' => $this->children
        ];
    }
}
