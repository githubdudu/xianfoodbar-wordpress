<?php

namespace App\Core;

use ArrayAccess;
use JsonSerializable;

class TableLayout implements JsonSerializable
{
    // 表格标题
    private string $title;
    // 子标题
    private string $sub_title;
    // 布局信息
    private array $layouts = [
        'column' => [],
        /**
         * 自定义Button的部分
         * text: 文本内容 字符串
         * icon: 图标 字符串
         * color: 颜色 字符串
         * changeText: 替换文本内容 字符串
         * dialog: 弹窗 布尔类型
         * forms: 自定义表单 数组或对象
         * className: css类名 字符串
         * link: 点击后跳转的链接
         * ajax: 点击后的ajax的链接 不可以dialog和forms重合 字符串
         * ajaxType: 传输方式，默认POST 字符串
         * ajaxIncludeData: 传输需要补充的数据，默认只传输ID 数组
         * changeRule: 显示的条件 字符串
         */
        'buttons_link' => [
            // 'edit' => [
            //     'dialog' => true,
            //     'forms' => []
            // ],
            // 'delete' => true,
            // 'view' => [
            //     'dialog' => true,
            //     'forms' => []
            // ]
        ],
        'tool_buttons' => [],
        /**
         * 自定义Button的部分
         * text: 文本内容 字符串
         * icon: 图标 字符串
         * color: 颜色 字符串
         * changeText: 替换文本内容 字符串
         * dialog: 弹窗 布尔类型
         * forms: 自定义表单 数组或对象
         * className: css类名 字符串
         * link: 点击后跳转的链接
         * ajax: 点击后的ajax的链接 不可以dialog和forms重合 字符串
         * ajaxType: 传输方式，默认POST 字符串
         * ajaxIncludeData: 传输需要补充的数据，默认只传输ID 字符串
         * changeRule: 显示的条件 字符串
         */
        'addButtonInfo' => [
            'text' => '添加',
            'icon' => 'PlusOutlined'
        ],
        'alert_buttons' => [],

    ];
    private bool $showSelect = false;
    // 表格描述
    private string $description = "";
    // 接口列表
    private array $apiList = [
        'data' => "",
        'view' => "",
        'delete' => "",
    ];

    public function __construct(string $title = "", string $subTitle = "", string $description = "")
    {
        $this->title = $title;
        $this->sub_title = $subTitle;
        $this->description = $description;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSubTitle()
    {
        return $this->sub_title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 获取指定接口信息
     *
     * @param string $dataIndex
     * @return string|bool
     */
    public function getApiLinks(string $dataIndex)
    {
        if (!isset($this->apiList[$dataIndex])) {
            return false;
        }
        return $this->apiList[$dataIndex];
    }

    /**
     * 添加column
     *
     * @param string|int $key
     * @param string $dataIndex
     * @param string $showTitle
     * @param string|array $render
     * @param array $options
     * @return TableLayout
     */
    public function addCol(string $dataIndex = "", string $showTitle = "", $render = null, array $options = []): TableLayout
    {
        $options = array_merge([
            'key' => $dataIndex,
            'dataIndex' => $dataIndex,
            'title' => $showTitle,
        ], $options);

        if ($render !== null) {
            $options['render'] = $render;
        }

        $this->layouts['column'][] = $options;
        return $this;
    }

    /**
     * 设置相关按钮的内容
     *
     * @param TableButtonLayout $buttonSetting
     * @return TableLayout
     */
    public function addButton(TableButtonLayout $buttonSetting): TableLayout
    {
        // $buttonSetting['name'] = $buttonIndex;
        $this->layouts['buttons_link'][] = $buttonSetting;
        return $this;
    }

    /**
     * 设置工具栏相关按钮的内容
     *
     * @param TableButtonLayout $buttonSetting
     * @return TableLayout
     */
    public function addToolButton(TableButtonLayout $buttonSetting): TableLayout
    {
        // $buttonSetting['name'] = $buttonIndex;
        $this->layouts['tool_buttons'][] = $buttonSetting;
        return $this;
    }

    /**
     * 设置工具栏相关按钮的内容
     *
     * @param TableButtonLayout $buttonSetting
     * @return TableLayout
     */
    public function addAlertButton(TableButtonLayout $buttonSetting): TableLayout
    {
        // $buttonSetting['name'] = $buttonIndex;
        $this->layouts['alert_buttons'][] = $buttonSetting;
        return $this;
    }

    /**
     * 设置接口相关
     *
     * @param string $apiIndex
     * @param string $apiLink
     * @return TableLayout
     */
    public function changeApilink(string $apiIndex, string $apiLink = ""): TableLayout
    {
        $this->apiList[$apiIndex] = $apiLink;
        return $this;
    }

    public function showSelect(): TableLayout
    {
        $this->showSelect = true;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'description' => $this->description,
            'column' => $this->layouts['column'],
            'addButton' => $this->layouts['addButtonInfo'] ?: null,
            'buttons' => $this->layouts['buttons_link'],
            'tool_buttons' => $this->layouts['tool_buttons'],
            'api_list' => $this->apiList,
            'showSelect' => $this->showSelect,
            'alert_buttons' => $this->layouts['alert_buttons']
        ];
    }
}
