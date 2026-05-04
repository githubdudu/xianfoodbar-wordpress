<?php

namespace App\Controller\Admin;

use App\Core\Controller\Wordpress;
use App\Core\Schema;
use App\Core\TableButtonLayout;
use App\Core\TableLayout;
use App\Core\Ui\TransformForm;
use App\Form\MenuCategoryType;
use App\Model\MenuCategory;
use App\Service\AdminLogger;
use App\Service\AdminMenuGenerator;
use Illuminate\Support\Facades\Date;
use Symfony\Component\Routing\Annotation\Route;

class MenuCategoryController extends Wordpress
{
    public function initAdminMenu(AdminMenuGenerator $menu)
    {
        if ($this->isAdmin()) {
            $menu->addMenus($this->generateTableArray('menuCategory', 'list'), '菜品分类', useRouter: true);
        }
    }

    public function list(): \Symfony\Component\HttpFoundation\Response
    {
        $addButtons = new TableButtonLayout();
        $addButtons->setText('添加分类')
            ->setLink($this->generateFormUrl('menuCategory', 'form'))
            ->isRouter();

        $editButtons = new TableButtonLayout();
        $editButtons->setText('修改分类')
            ->setLink($this->generateFormUrl('menuCategory', 'form'))
            ->isRouter();

        $delButtons = new TableButtonLayout();
        $delButtons->setText('删除分类')
            ->setAjax($this->generateUrl('api_admin_menu_cate_delete'))
            ->setColor('#d64a4a');

        $tableLayout = new TableLayout('菜品分类', '菜品分类');
        $tableLayout->changeApilink('data', $this->generateUrl('api_admin_menu_category'));

//        $transform = new TransformTable(MenuCategory::class, $tableLayout);
//        $transform->export();

        $tableLayout->addCol('id', 'ID');
        $tableLayout->addCol('category_name', '分类名称');


        $tableLayout->addToolButton($addButtons);
        $tableLayout->addButton($editButtons)->addButton($delButtons);

        return $this->tableTemplate($tableLayout);
    }

    #[Route("/api/admin/menu-cate/delete/{id}", name: 'api_admin_menu_cate_delete')]
    public function delte(int $id = 0)
    {
        if ($this->isAdmin()) {
            $menu = MenuCategory::find($id);

            if ($menu) {
                $menu->delete();
                return $this->sendJson('操作成功');
            }

            return $this->sendJson('not found', 404);
        }

        return $this->sendJson('权限不够', 403);
    }

    public function form(?string $id = null)
    {
        $schema = new Schema("创建分类", '创建菜品分类');
        $schema->setPostApiAddress($this->generateUrl('api_admin_add_category'))
            ->setEditApiAddress($this->generateUrl('api_admin_edit_category'));
        if ($id > 0) {
            $schema->setTitle('修改分类');
            $schema->setSubTitle('修改分类');
            $schema->setNowType('edit');
            $category = MenuCategory::find($id);
            $schema->setFormData($category);

        }
        $schema->transform($this->createForm(MenuCategoryType::class));

        return $this->formTamplate($schema);
    }

    #[Route('/api/admin/category/add', name: 'api_admin_add_category')]
    public function addCate(AdminLogger $logger)
    {
        if ($this->isAdmin()) {
            $cate_name = $this->request->request->get('category_name', '');

            $category = new MenuCategory();
            $category->category_name = $cate_name;
            $category->create_time = new \DateTime();

            $res = $category->save();

            if ($res) {
                return $this->sendJson('添加成功', 200);
            }
            return $this->sendJson('添加失败', 500);
        }
        return $this->sendJson('权限不够', 403);
    }

    #[Route('/api/admin/category/edit/{id}', name: 'api_admin_edit_category')]
    public function editCate(AdminLogger $logger, int $id = 0)
    {
        if ($this->isAdmin()) {
            $cate_name = $this->request->request->get('category_name', '');

            $category = MenuCategory::find($id);

            if ($category) {
                $category->category_name = $cate_name;
                $category->create_time = new \DateTime();

                $res = $category->update();

                if ($res) {
                    return $this->sendJson('添加成功', 200);
                }
                return $this->sendJson('添加失败', 500);
            }
            return $this->sendJson('未知分类', 404);
        }
        return $this->sendJson('权限不够', 403);
    }

    #[Route('/api/admin/menucate/list', name: 'api_admin_menu_category')]
    public function dataList()
    {
        if ($this->isAdmin()) {
            $this->addJsonData('data', MenuCategory::all());
            $this->addJsonData('total', 0);
            return $this->sendJson();
        }
        return $this->sendJson('权限不够', 403);
    }
}
