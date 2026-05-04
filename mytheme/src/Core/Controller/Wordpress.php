<?php

namespace App\Core\Controller;

use App\Core\WordPressFunc;
use App\Service\AdminRequests;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Asset\Package;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WP_Post;

class Wordpress extends CoreAdminController
{
    use WordPressFunc;

    #[Route("/api/user/avatar/{user_id}", name: "user_avatar")]
    public function wp_base_api_get_head($user_id = 0)
    {
        return new Response('', 404);

        $head = get_user_meta($user_id, 'head', true);
        if (empty($head)) {
            $head = 'https://api.multiavatar.com/Starcrasher.png';
        } else {
            $head = trim(get_site_url(), '/') . '/' . $head;
        }

        $client = new CurlHttpClient();
        $head = $client->request('GET', $head, [
            'verify_peer' => false,
            'verify_host' => false,
            // 'extra' => [
            //     'curl' => [
            //         CURLOPT_SSL_VERIFYPEER => false,
            //         CURLOPT_SSL_VERIFYHOST => false,
            //     ]
            // ]
        ]);
        $info = getimagesizefromstring($head->getContent());

        return new Response($head->getContent(), 200, [
            'Content-Type' => $info['mime']
        ]);
    }

    #[Route("/api/upload", name: "wp_upload_file_api")]
    #[Route("/api/upload/{uid}", name: "wp_upload_file_api_by_uid")]
    public function wp_api_base_upload_admin($uid = 0)
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        if (!function_exists('wp_create_image_subsizes')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        /**
        * @var UploadedFile
        */
        $fileInfo = $this->request->files->get('file');
        $args = [];
        $fileName = "";
        if ($fileInfo instanceof UploadedFile) {
            $args = [
                'post_title' => $fileInfo->getClientOriginalName(),
                'post_mime_type' => $fileInfo->getType(),
            ];
            $fileName = $fileInfo->getClientOriginalName();
        } else {
            $args = [
                'post_title' => $fileInfo['name'],
                'post_mime_type' => $fileInfo['type'],
            ];
            $fileName = $fileInfo['name'];
        }

        $fileSystem = new Filesystem();
        $assetPackage = new Package(new EmptyVersionStrategy());

        $now_uri = $this->request->getUri();

        if (strpos($now_uri, 'upload_admin') !== false) {
            $uid = 1;
        }

        if ($uid) {
            $args['author'] = $uid;
            $args['post_author'] = $uid;
        }
        $file = $_FILES['file'];
        $file = wp_handle_upload($file, ['test_form' => false]);

        if (empty($file)) {
            return $this->sendJson('', 500);
        }

        $id = wp_insert_attachment($args, $file['file']);

        if (isset($file['file']) && strpos($file['type'], 'image') !== false) {
            wp_create_image_subsizes($file['file'], $id);
        }

        return $this->json([
            'status' => 'ok',
            'code' => 200,
            'name' => $fileInfo instanceof UploadedFile ? $fileInfo->getClientOriginalName() : $fileInfo['name'],
            'thumbUrl' => $file['url'],
            'url' => $file['url'],
        ]);
    }

    protected function isLogin()
    {
        // if (is_file(dirname(dirname(__DIR__)) . '/logined')) {
        //     return true;
        // }
        return is_user_logged_in();
    }
}
