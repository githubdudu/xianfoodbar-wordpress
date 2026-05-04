<?php

namespace App\Controller\System;

use App\Core\CheckType;
use App\Core\CoreAdminController;
use App\Core\Schema;
use App\Core\Controller\Wordpress;
use App\Form\AccountType;
use App\Form\AdminLogin;
use DateTime;
use Laminas\Db\Sql\Update;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update as MercureUpdate;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class AdminController extends Wordpress
{
    public function initialize()
    {
        if (!$this->isLogin()) {
            (new RedirectResponse($this->router->generate("admin_login")))->send();
        }
    }
    /**
     * 后台主界面
     *
     * @return Response
     */
    #[Route('/adminpanel', name: 'admin_base')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route("/adminpanel/logger/list", name: 'admin_logs_list')]
    public function LoggerList(): Response
    {
        return $this->render('admin/login.html.twig', []);
    }

    /**
     * 后台表格模板类型
     * 
     * @return Response
     */
    #[Route('/adminpanel/system/table/{name}/{method}', name: 'admin_system_table_show')]
    #[Route('/adminpanel/system/table/{name}/{method}/{arguments}', name: 'admin_system_table_show_argument')]
    #[Route('/adminpanel/system/tabs/{name}/{method}', name: 'admin_system_tabs_show')]
    #[Route('/adminpanel/system/tabs/{name}/{method}/{arguments}', name: 'admin_system_tabs_show_argument')]
    #[Route('/adminpanel/system/form/{name}/{method}', name: 'admin_system_form_show', methods: ['GET', 'POST', 'PATCH', 'PUT'])]
    #[Route('/adminpanel/system/form/{name}/{method}/{arguments}', name: 'admin_system_form_show_argument', methods: ['GET', 'POST', 'PATCH', 'PUT'])]
    public function TableListShow(string $name = '', string $method = '', string|int|float|null $arguments = null): Response
    {
        if ($this->isLogin() && $name) {
            $name = "App\\Controller\\Admin\\" . ucfirst($name) . "Controller";
            if (class_exists("\\" . $name)) {
                CheckType::setHtmlType(true);
                return $this->forward($name . '::' . $method, [
                    'id' => $arguments
                ]);
            }
        }

        return $this->NotFoundLayout();
    }

    /**
     * 后台表格配置信息
     * 
     * @return Response
     */
    #[Route('/api/admin/system/table/config/{name}/{method}', name: 'admin_system_table_config_api')]
    #[Route('/api/admin/system/forms/config/{name}/{method}', name: 'admin_system_forms_config_api')]
    #[Route('/api/admin/system/tabs/config/{name}/{method}', name: 'admin_system_tabs_config_api')]
    #[Route('/api/admin/system/table/config/{name}/{method}/{argument}', name: 'admin_system_table_config_api_argument')]
    #[Route('/api/admin/system/forms/config/{name}/{method}/{argument}', name: 'admin_system_forms_config_api_argument')]
    #[Route('/api/admin/system/tabs/config/{name}/{method}/{argument}', name: 'admin_system_tabs_config_api_argument')]
    public function TableConfigShow($name = '', $method = '', $argument = '',): Response
    {
        if (!$this->isLogin()) {
            return $this->sendJson('', 403);
        }
        if ($name && $method) {
            $name = "App\\Controller\\Admin\\" . ucfirst($name) . "Controller";
            if (class_exists("\\" . $name)) {
                CheckType::setHtmlType(false);
                return $this->forward($name . '::' . $method, [
                    'id' => $argument
                ]);
            }
        }

        return $this->sendJson('not found', 404);
    }

    /**
     * 后台表格模板类型
     * 
     * @return Response
     */
    #[Route('/adminpanel/plugin/table/{name}/{method}', name: 'admin_plugin_table_show')]
    public function PluginTableListShow($name = '', $method = ''): Response
    {
        if ($name) {
        }

        return $this->NotFoundLayout();
    }

    /**
     * 清除缓存
     *
     * @param KernelInterface $kernel
     * @return Response
     */
    #[Route('/api/admin/clear/cache', name: 'api_admin_clear_cache_api')]
    public function clearCaches(KernelInterface $kernel): Response
    {

        if ($this->isLogin()) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $command = new ArrayInput([
                'command' => 'cache:clear',
                '-e' => $kernel->getEnvironment(),
                '--no-warmup' => true,
            ]);

            $buff = new BufferedOutput();
            $application->run($command, $buff);
            $content = $buff->fetch();

            if (strpos($content, 'successfully cleared')) {
                return $this->sendJson('清除成功');
            }

            return $this->sendJson('清除失败', 500);
        }
        return $this->sendJson('', 404);
    }

    /**
     * 清除缓存
     *
     * @param KernelInterface $kernel
     * @return Response
     */
    #[Route('/api/admin/test', name: 'admin_clear_cache_api_test')]
    public function clearCacheTest(): Response
    {

        $root = new Schema();
        $form = $this->createForm(AccountType::class);
        $root->transform($form);

        return $this->json($root);
    }
}
