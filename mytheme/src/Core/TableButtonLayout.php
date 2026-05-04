<?php

namespace App\Core;

use JsonSerializable;

class TableButtonLayout implements JsonSerializable
{
    /**
     * 文本内容(显示的文本)
     */
    private string $text = "";
    /**
     * 文字前的图标
     */
    private string $icon = "";
    /**
     * 背景色和边框色
     */
    private string $color = "";
    /**
     * rule成立后，替换的文本内容
     */
    private string $changeText = "";
    /**
     * 以弹窗的方式显示
     */
    private bool $dialog = false;
    /**
     * 弹窗时显示的自定义表单
     */
    private array $forms = [];
    /**
     * css类名
     */
    private string $className = "";
    /**
     * 点击后跳转的链接
     */
    private string $link = "";
    /**
     * 点击后的ajax的链接 不可以dialog和forms重合
     */
    private string $ajax = "";
    private string $ajaxId = "";
    /**
     * 传输方式，默认POST
     */
    private string $ajaxType = 'POST';
    /**
     * 传输需要补充的数据，默认只传输ID
     */
    private array  $ajaxIncludeData = [];
    /**
     * 显示替换文本的条件
     */
    private string $changeRule = "";

    private bool $isRouter = false;

    /**
     * 设置按钮的名字
     *
     * @param string $text 文本内容
     * @return TableButtonLayout
     */
    public function setText(string $text): TableButtonLayout
    {
        $this->text = $text;
        return $this;
    }

    /**
     * 获取按钮的名字
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * 设置按钮图标
     *
     * @param string $icon 图标名称
     * @return TableButtonLayout
     */
    public function setIcon(string $icon): TableButtonLayout
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * 获取按钮的图标
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * 设置背景和边框颜色
     *
     * @param string $color 颜色代码
     * @return TableButtonLayout
     */
    public function setColor(string $color): TableButtonLayout
    {
        $this->color = $color;
        return $this;
    }

    /**
     * 获取按钮的名字
     *
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * 非Ajax情况，点击后跳转的链接
     *
     * @param string $link 跳转链接
     * @return TableButtonLayout
     */
    public function setLink(string $link): TableButtonLayout
    {
        $this->link = $link;
        return $this;
    }

    /**
     * 获取跳转链接
     *
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * 设置ajax请求链接
     *
     * @param string $ajax 请求链接
     * @return TableButtonLayout
     */
    public function setAjax(string $ajax): TableButtonLayout
    {
        $this->ajax = $ajax;
        return $this;
    }

    public function setAjaxInclude(array $ajax): TableButtonLayout
    {
        $this->ajaxIncludeData = $ajax;
        return $this;
    }

    /**
     * 设置ajax请求链接
     *
     * @param string $ajax 请求链接
     * @return TableButtonLayout
     */
    public function setAjaxId(string $ajaxId): TableButtonLayout
    {
        $this->ajaxId = $ajaxId;
        return $this;
    }

    /**
     * 获取ajax请求链接
     *
     * @return string
     */
    public function getAjax(): string
    {
        return $this->ajax;
    }

    /**
     * 设置Ajax请求类型
     *
     * @param string $ajaxType 请求类型
     * @return TableButtonLayout
     */
    public function setAjaxType(string $ajaxType): TableButtonLayout
    {
        $this->ajaxType = $ajaxType;
        return $this;
    }

    /**
     * 获取Ajax请求类型
     *
     * @return string
     */
    public function getAjaxType(): string
    {
        return $this->ajaxType;
    }

    /**
     * 设置Class类
     *
     * @param string $className Class类字符串
     * @return TableButtonLayout
     */
    public function setClassName(string $className): TableButtonLayout
    {
        $this->className = $className;
        return $this;
    }

    /**
     * 获取Class类
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * 设置文本显示不同时的特定条件
     *
     * @param string $changeRule js代码，条件
     * @return TableButtonLayout
     */
    public function setChangeRule(string $changeRule): TableButtonLayout
    {
        $this->changeRule = $changeRule;
        return $this;
    }

    /**
     * 获取特定条件
     *
     * @return string
     */
    public function getChangeRule(): string
    {
        return $this->changeRule;
    }

    /**
     * 设置替换文本的名字
     *
     * @param string $changeText 文本内容
     * @return TableButtonLayout
     */
    public function setChangeText(string $changeText): TableButtonLayout
    {
        $this->changeText = $changeText;
        return $this;
    }

    /**
     * 获取替换文本的名字
     *
     * @return string|null
     */
    public function getChangeText(): string
    {
        return $this->changeText;
    }

    /**
     * 设置弹窗时的表单数组
     *
     * @param array $forms 表单数组
     * @return TableButtonLayout
     */
    public function setForms(array $forms): TableButtonLayout
    {
        $this->forms = $forms;
        return $this;
    }

    /**
     * 获取表单数组
     *
     * @return string|null
     */
    public function getForms(): array
    {
        return $this->forms;
    }

    /**
     * 设置为Dialog模式
     *
     * @return TableButtonLayout
     */
    public function isDialog(): TableButtonLayout
    {
        $this->dialog = true;
        return $this;
    }

    /**
     * 设置为router
     *
     * @return TableButtonLayout
     */
    public function isRouter(): TableButtonLayout
    {
        $this->isRouter = true;
        return $this;
    }

    /**
     * 获取弹窗信息
     *
     * @return boolean
     */
    public function getDialogStatus(): bool
    {
        return $this->dialog;
    }

    public function jsonSerialize(): mixed
    {
        $arrayData = [
            'text' => $this->text,
            'dialog' => $this->dialog,
            'isRouter' => false,
        ];
        if (!empty($this->icon)) {
            $arrayData['icon'] = $this->icon;
        }

        if (!empty($this->color)) {
            $arrayData['color'] = $this->color;
        }

        if (!empty($this->changeText)) {
            $arrayData['changeText'] = $this->changeText;
        }

        if (!empty($this->forms)) {
            $arrayData['forms'] = $this->forms;
        }

        if (!empty($this->className)) {
            $arrayData['className'] = $this->className;
        }

        if (!empty($this->link)) {
            $arrayData['link'] = $this->link;
        }

        if (!empty($this->ajax)) {
            $arrayData['ajax'] = $this->ajax;
        }

        if (!empty($this->ajaxId)) {
            $arrayData['ajaxId'] = $this->ajaxId;
        }

        if (!empty($this->ajaxType)) {
            $arrayData['ajaxType'] = $this->ajaxType;
        }

        if (!empty($this->ajaxIncludeData)) {
            $arrayData['ajaxIncludeData'] = $this->ajaxIncludeData;
        }

        if (!empty($this->changeRule)) {
            $arrayData['changeRule'] = $this->changeRule;
        }

        if ($this->isRouter) {
            $arrayData['isRouter'] = true;
        }

        return $arrayData;
    }
}
