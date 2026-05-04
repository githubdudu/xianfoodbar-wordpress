<?php

namespace App\Core\Controller;

use App\Core\CheckType;
use App\Core\Schema;
use App\Core\SchemaLayout\ObjectSchema;
use App\Core\TableLayout;
use App\Core\TabsLayout;
use DateTimeImmutable;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

abstract class CoreAdminController extends RESTController
{
    /**
     * 站点标题
     *
     * @var string
     */
    private string $CoreAdminTitle = "";
    private bool $NowHtmlType = false;
    private mixed $FormArguments;

    /**
     * 设置当前类型
     *
     * @param boolean $html
     * @return void
     */
    public function setNowType(bool $html = true)
    {
        $this->NowHtmlType = $html;
    }

    /**
     * 获取是否为html类型
     *
     * @return boolean
     */
    public function NowisHtmlType()
    {
        return $this->NowHtmlType;
    }

    /**
     * 表单时的参数
     *
     * @param mixed $argus
     * @return void
     */
    public function setHtmlArguments(mixed $argus)
    {
        $this->FormArguments = $argus;
    }

    /**
     * 获取表单时候的参数
     *
     * @return mixed
     */
    public function getHtmlArgumens()
    {
        return $this->FormArguments;
    }

    /**
     * 表格模板
     *
     * @return Response
     */
    public function tableTemplate(TableLayout $tables): Response
    {

        if (CheckType::isHtmlType()) {
            return $this->render("admin/table.html.twig", [
                'title' => $this->CoreAdminTitle,
                'siteConfig' => $tables,
            ]);
        }

        $this->addJsonData('config', $tables);
        return $this->sendJson();
    }

    /**
     * 表格模板
     *
     * @return Response
     */
    public function tabsTemplate(TabsLayout $configs): Response
    {
        if (CheckType::isHtmlType()) {
            return $this->render("admin/tabs.html.twig", [
                'title' => $this->CoreAdminTitle,
                'siteConfig' => $configs,
            ]);
        }

        $this->addJsonData('config', $configs);
        return $this->sendJson();
    }

    /**
     * 表单模板
     *
     * @return Response
     */
    public function formTamplate(Schema $schema): Response
    {

        if (CheckType::isHtmlType()) {
            return $this->render("admin/forms.html.twig", [
                'title' => $this->CoreAdminTitle,
                'siteConfig' => $schema,
            ]);
        }

        $this->addJsonData('config', $schema);
        return $this->sendJson();
    }

    /**
     * 404页面模板
     *
     * @param string $title 页面标题
     * @return Response
     */
    public function NotFoundLayout(string $title = "没有找到页面"): Response
    {
        if (empty($this->CoreAdminTitle)) {
            $this->setTitle($title);
        }

        return $this->render('admin/404.html.twig', [
            'title' => $this->CoreAdminTitle
        ]);
    }

    /**
     * 设置站点标题
     *
     * @param string $titleName
     * @return void
     */
    public function setTitle(string $titleName = "")
    {
        $this->CoreAdminTitle = $titleName;
    }

    /**
     * 获取标题
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->CoreAdminTitle;
    }

    /**
     * 生成表格的链接
     *
     * @param string $name 路径名
     * @param mixed ...$arguments 参数
     * @return string
     */
    public function generateTableUrl(string $name, mixed ...$arguments): string
    {
        return $this->router->generate('admin_system_table_show', [
            'name' => $name,
        ]);
    }

    /**
     * 生成表格的链接
     *
     * @param string $name 路径名
     * @param mixed ...$arguments 参数
     * @return string
     */
    public function generateFormUrl(string $name, string $method, mixed ...$arguments): string
    {
        return $this->router->generate('admin_system_form_show', [
            'name' => $name,
            'method' => $method,
            ...$arguments,
        ]);
    }

    /**
     * 生成表格的链接
     *
     * @param string $name 路径名
     * @param mixed ...$arguments 参数
     * @return string
     */
    public function generateTableArray(string $name, string $method, mixed ...$arguments): array
    {
        return ['admin_system_table_show', [
            'name' => $name,
            'method' => $method,
            ...$arguments,
        ]];
    }

    public function generateTabsArray(string $name, string $method, mixed ...$arguments): array
    {
        return ['admin_system_tabs_show', [
            'name' => $name,
            'method' => $method,
            ...$arguments,
        ]];
    }

    /**
     * 生成表格的链接
     *
     * @param string $name 路径名
     * @param mixed ...$arguments 参数
     * @return string
     */
    public function generateFormArray(string $name, string $method, mixed ...$arguments): array
    {
        return ['admin_system_form_show', [
            'name' => $name,
            'method' => $method,
            ...$arguments,
        ]];
    }

    public function parserStrToTimestamp(string $time, int $baseTime = 0): string|int|null
    {
        $date = new DateTimeImmutable($time);
        if ($baseTime > 0) {
            $date = $date->setTimestamp($baseTime);
        }
        if ($date) {
            return $date->getTimestamp();
        }
        return null;
    }
}
