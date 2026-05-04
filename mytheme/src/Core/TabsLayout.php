<?php

namespace App\Core;

use JsonSerializable;

final class TabsLayout implements JsonSerializable
{
    /**
     * Tab标签
     */
    private array $tabs = [];
    /**
     * Api数据地址
     */
    private array $apiLinks = [];

    /**
     * @param string $title 标题
     * @param string $subTitle 副标题
     * @param string $Header 描述
     */
    public function __construct(
        private string $title,
        private string $subTitle,
        private string $Header = "",
    ) {

    }


    /**
     * 添加标签页
     *
     * @param string $title 标签的标题
     * @param string $tags 标题的标志
     * @param string $apiLink 请求链接
     * @param bool $isChecked = false  是否选中
     * @param int $badge = 0 描述的数量
     * @param bool $isLongRequest = false 是否长连接 EventSource
     * @return TabsLayout
     */
    public function addTabs(
        string $title,
        string $tags,
        string $apiLink,
        bool $isChecked = false,
        int $badge = 0,
        bool $isLongRequest = false,
    ): TabsLayout {
        $this->tabs[] = [
            'title' => $title,
            'name' => $tags,
            'badge' => $badge,
            'checked' => $isChecked
        ];

        $this->apiLinks[$tags] = [
            'url' => $apiLink,
            'isLong' => $isLongRequest
        ];

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'title' => $this->title,
            'sub_title' => $this->subTitle,
            'Header' => $this->Header,
            'tabs' => $this->tabs,
            'apiList' => $this->apiLinks,
        ];
    }
}
