<?php

namespace App\Controller\System;

use App\Core\CoreAdminController;
use App\Core\Controller\Wordpress;
use Spatie\Ssr\Engines\Node;
use Spatie\Ssr\Renderer;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminLoginController extends Wordpress
{

    /**
     * 用户登录
     *
     * @return Response
     */
    #[Route("/adminpanel/login", name: 'admin_login')]
    public function login(): Response
    {
        // var_dump($form->all());
        return $this->render('admin/login.html.twig', [
            'forms' => '',
        ]);
    }

    #[Route('/api/admin/account/login', name: 'api_admin_login')]
    public function loginCheck(): Response
    {
        if ($this->request->isMethod('POST')) {
            $account = $this->request->request->get('account_name', '');
            $password = $this->request->request->get('account_password', '');
            $csrftokenName = "admin-login";
            $csrftokenValuue = $this->request->request->get('csrf_token');

            if ($this->isCsrfTokenValid($csrftokenName, $csrftokenValuue)) {

                $result = $this->WpLogin($account, $password);
                $this->addJsonData('sss', [
                    $account,
                    $password
                ]);
                if (is_wp_error($result)) {
                    return $this->sendJson($result->get_error_message($result->get_error_code()), 500);
                }
                file_put_contents(dirname(dirname(__DIR__)) . '/logined', '');
                return $this->sendJson();
            }
        }
        return $this->sendJson('非法访问', 404);
    }

    #[Route('/adminpanel/logout', name: 'api_admin_logout')]
    public function logoutSystem() {
        wp_logout();
        @unlink(dirname(dirname(__DIR__)) . '/logined');
        return new RedirectResponse($this->generateUrl('admin_login'));
    }
}
