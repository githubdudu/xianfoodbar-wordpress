<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AdminMenuGenerator
{
    private $logger;
    private $urlGenerator;
    private array $meuns = [];

    public function __construct(LoggerInterface $loggerInterface, UrlGeneratorInterface $url, 
    private RouterInterface $router)
    {
        $this->logger = $loggerInterface;
        $this->urlGenerator = $url;
    }

    public function getMenus(): array
    {
        $this->defaultMenus();
        return $this->meuns;
    }

    public function getRouter(): RouterInterface {
        return $this->router;
    }

    public function addMenus($routeName, string $menuName = "", array $iconName = [
        'name' => 'UserOutlined', // String see Antd Icons
        'color' => '', // String
        'style' => [], // JSON
    ], array $childs = [],bool $return = false, bool $useRouter = false, bool $useBlank = false, int $index = 0)
    {

        if (!empty($routeName)) {
            if (is_array($routeName)) {
                $routeName = $this->urlGenerator->generate($routeName[0], $routeName[1]);
            } else {
                $routeName = $this->urlGenerator->generate($routeName);
            }
        }

        $temp = [
            'path' => $routeName,
            'name' => $menuName,
            'icon' => $iconName,
            'key' => $menuName . rand(0, 100),
            'useLink' => $useRouter,
            'useBlank' => $useBlank
        ];

        if (!empty($childs)) {
            $temp['children'] = $childs;
        }

        if ($return) {
            return $temp;
        }

        if ($index === 0) {
            array_splice($this->meuns, 0, 0, [$temp]);
        } else {
            array_splice($this->meuns, $index, 0, [$temp]);
        }

        
    }

    private function defaultMenus()
    {
        // $this->addMenus('', '账号管理', [
        //     'name' => 'UserOutlined'
        // ], [
        //     $this->addMenus(['admin_system_table_show', ['name' => 'account', 'method' => 'list']], '账号列表', [
        //         'name' => 'UnorderedListOutlined'
        //     ], return: true, useRouter: true),
        //     $this->addMenus(['admin_system_table_show', ['name' => 'account', 'type' => 1]], '回收站列表', [
        //         'name' => 'DeleteOutlined'
        //     ], return: true),
        // ]);

        // $this->addMenus(['admin_system_table_show', ['name' => 'logs']], '日志管理', [
        //     'name' => 'SettingOutlined'
        // ]);
    }
}
