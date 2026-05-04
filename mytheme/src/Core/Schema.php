<?php

namespace App\Core;

use App\Core\SchemaLayout\JsonSchema;
use App\Core\SchemaLayout\ObjectSchema;
use App\Core\SchemaLayout\StringSchema;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use JsonSerializable;

class Schema implements JsonSerializable
{
    // 表格标题
    private string $title;

    // 子标题
    private string $sub_title;

    // 布局信息
    private array $schema = [
        'id' => 0,
        'now_type' => 'post',
        'schema' => [],
        'showDescIcon' => false,
        'readOnly' => false,
        'column' => 1,
        'labelWidth' => 120,
        'displayType' => 'column',
        'post' => [
            'api' => '',
            'method' => 'POST',
        ],
        'edit' => [
            'api' => '',
            'method' => 'PUT',
        ],
        'return_url' => '',
        'formData' => '',
    ];

    // 表格描述
    private string $description = '';

    public function __construct(string $title = '', string $subTitle = '', string $description = '')
    {
        $this->title = $title;
        $this->sub_title = $subTitle;
        $this->description = $description;
        $this->schema['formData'] = new class() {};
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function setSubTitle(string $subTitle)
    {
        $this->sub_title = $subTitle;
        return $this;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * 设置当前模式
     *
     * @param string $now_type
     * @return Schema
     */
    public function setNowType(string $now_type = 'post'): Schema
    {
        $this->schema['now_type'] = $now_type;
        return $this;
    }

    /**
     * 设置当前ID
     *
     * @param string $id
     * @return Schema
     */
    public function setId(string $id = '0'): Schema
    {
        $this->schema['id'] = $id;
        return $this;
    }

    public function setSchema(SchemaLayout $schemaLayout): Schema
    {
        $this->schema['schema'] = $schemaLayout;
        return $this;
    }

    public function getSchema(): SchemaLayout|ObjectSchema
    {
        return $this->schema['schema'];
    }

    public function setReturnUrl(string $return_url): Schema
    {
        $this->schema['return_url'] = $return_url;
        return $this;
    }

    /**
     * 设置API接口
     *
     * @param string $apiAddress
     * @return Schema
     */
    public function setPostApiAddress(string $apiAddress = ''): Schema
    {
        $this->schema['post']['api'] = $apiAddress;
        return $this;
    }

    /**
     * 设置修改API接口
     *
     * @param string $apiAddress
     * @return Schema
     */
    public function setEditApiAddress(string $apiAddress = ''): Schema
    {
        $this->schema['edit']['api'] = $apiAddress;
        return $this;
    }

    /**
     * 设置提交的方式
     *
     * @return Schema
     */
    public function setPostMethod(string $method = 'POST'): Schema
    {
        $this->schema['post']['method'] = $method;
        return $this;
    }

    /**
     * 设置修改的提交方式
     *
     * @return Schema
     */
    public function setEditMethod(string $method = 'POST'): Schema
    {
        $this->schema['edit']['method'] = $method;
        return $this;
    }

    /**
     * label 是否与表单元素同行（row 为同行）
     * 默认是 column
     *
     * @param string $type 'row' / 'column'
     * @return Schema
     */
    public function setDisplayType(string $type): Schema
    {
        $this->schema['displayType'] = $type;
        return $this;
    }

    /**
     * 描述用气泡展示
     *
     * @param boolean $showDescIcon
     * @return Schema
     */
    public function setShowDescIcon(bool $showDescIcon): Schema
    {
        $this->schema['showDescIcon'] = $showDescIcon;
        return $this;
    }

    /**
     * 只读模式
     *
     * @return Schema
     */
    public function setReadOnly(): Schema
    {
        $this->schema['readOnly'] = true;
        return $this;
    }

    /**
     * 表单现在的值
     *
     * @param array|object $formData
     * @return Schema
     */
    public function setFormData(array|object $formData): Schema
    {
        $this->schema['formData'] = $formData;
        return $this;
    }

    /**
     * 表单元素的 label 的宽度
     *
     * @param array $labelWidth
     * @return Schema
     */
    public function setlabelWidth(array $labelWidth): Schema
    {
        $this->schema['labelWidth'] = $labelWidth;
        return $this;
    }

    /**
     * 如果希望统一的一行展示 n 个元�
     *
     * @param integer $column
     * @return Schema
     */
    public function setColumn(int $column): Schema
    {
        $this->schema['column'] = $column;
        return $this;
    }

    public function __toString()
    {
        return json_encode($this->schema);
    }

    public function transform(FormInterface $forms)
    {
        $rootObject = new ObjectSchema();
        $rootObject = $this->loopTrans($forms->all(), $rootObject);

        $this->schema['schema'] = $rootObject;
    }

    /**
     * Undocumented function
     *
     * @param FormInterface[] $forms
     * @param ObjectSchema $root
     * @return ObjectSchema
     */
    public function loopTrans(array $forms, ObjectSchema $root)
    {
        foreach ($forms as $key => $form) {
            $type = $form->getConfig()->getType()->getInnerType();
            $options = $form->getConfig()->getOptions();

            // if ($options['required']) {
            //     unset($options['required']);
            //     $root->setRequired($key);
            // }

            $root->setProperties($key, new JsonSchema(array_filter($options, fn($v) => !empty(is_object($v) ? (array) $v : $v))));
        }
        return $root;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'description' => $this->description,
            'formConfig' => $this->schema,
        ];
    }
}
