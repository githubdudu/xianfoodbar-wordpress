<?php

namespace App\Controller\Api;

use App\Core\Controller\RESTController;
use App\Entity\Post;
use App\Form\PostType;
use App\Service\AdminMenuGenerator;
use ReflectionClass;
use Symfony\Component\Routing\Annotation\Route;

class AdminRestController extends RESTController
{
    #[Route('/api/admin/list', name: 'admin_rest')]
    public function index()
    {
    }

    #[Route('/api/admin/getMenus', name: 'admin_menu_list')]
    public function getMenu(AdminMenuGenerator $menus) {

        $filePath = [
            '../Admin/' => "App\\Controller\\Admin\\"
        ];

        foreach ($filePath as $path => $classNameSpace) {
            $fileList = scandir(realpath(__DIR__ . '/' . $path));

            foreach ($fileList as $file) {
                if ($file == '.' || $file == '..') continue;

                $baseName = str_replace(".php", "", $file);
                $class = $classNameSpace . "{$baseName}";

                

                if (class_exists('\\' . $class)) {
                    $classRef = new ReflectionClass($class);
                    if ($classRef->hasMethod('initAdminMenu')) {
                        $classRef->getMethod('initAdminMenu')->invoke($classRef->newInstanceWithoutConstructor(), $menus);
                        
                    }
                }
            }
        }

        $this->addJsonData('menu_list', $menus->getMenus());
        return $this->sendJson();
    }
}
