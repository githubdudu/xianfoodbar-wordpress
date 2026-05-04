<?php

namespace App\Entity;

use App\Core\Ui\Attribute\Form;
use App\Core\Ui\Attribute\TableCol;
use App\ORM\Data\DataSource;
use App\ORM\Entity;
use App\ORM\Mapping\Column;
use App\ORM\Mapping\Id;
use App\ORM\Mapping\Table;

/**
 * @ORM\Entity(repositoryClass=MenuCategoryRepository::class)
 */
#[Table('res_menu_category')]
#[Entity()]
class MenuCategory  extends DataSource
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Column(), Id()]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Column()]
    #[TableCol('分类名称', option: ["search" => false,])]
    #[Form('string', '分类名称', description: '统计用的分类名称', placeholder: '请输入分类名称', required: true)]
    private $category_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Column("datetime")]
    #[TableCol('创建时间', option: ["search" => false,])]
    private $create_time;

    /**
     * @ORM\Column(type="smallint")
     */
    #[Column('integer', 1)]
    #[TableCol('是否删除', option: [
        "valueEnum" => [
            1 => '是',
            0 => '否'
        ],
        'hideInTable' => true,
        "search" => false,
    ])]
    private $is_delete;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function setCategoryName(string $category_name): self
    {
        $this->category_name = $category_name;

        return $this;
    }

    public function getCreateTime(): ?string
    {
        return date('Y-m-d H:i:s',$this->create_time);
    }

    public function setCreateTime(?int $time = null): self
    {
        $this->create_time = $time ?? time();

        return $this;
    }

    public function getIsDelete(): ?int
    {
        return $this->is_delete;
    }

    public function setIsDelete(int $is_delete): self
    {
        $this->is_delete = $is_delete;

        return $this;
    }
}
