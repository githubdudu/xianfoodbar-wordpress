<?php

namespace App\Controller\System;

use App\Core\Controller\RESTController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends RESTController
{
    /**
     * 加载和下载文件
     */
    #[Route('/{donwload_or_public}/upload/{file}', requirements: ['file' => '.*$', 'donwload_or_public' => 'public|download'], name: 'get_static_files_and_download')]
    public function uploadStaticFile( KernelInterface $kernel, string $donwload_or_public = 'public', string $file = ''): Response {
        $fileSystem = new Filesystem();
        $file = $kernel->getProjectDir() . '/public/upload/' . $file;
        if (!$fileSystem->exists($file)) {
            throw $this->createNotFoundException();
        }
        
        $mime = new MimeTypes();
        $sendMime = $mime->getMimeTypes(pathinfo($file, PATHINFO_EXTENSION));
        $contentSize = filesize($file);

        $header = [
            'Content-Type' => $sendMime,
            'Content-Length' => $contentSize,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=31536000',
        ];

        if ($donwload_or_public === 'download') {
            $header['Content-Disposition'] = 'attachment; filename="' . basename($file) . '"';
        }

        return new Response(file_get_contents($file), 200, $header);
    }

    /**
     * 上传文件
     *
     * @return Response
     */
    #[Route('/api/upload', name: 'upload_file_api')]
    public function upload(KernelInterface $kernel): Response {
        /**
         * @var UploadedFile
         */
        $fileInfo = $this->request->files->get('file');
        $fileSystem = new Filesystem();

        $uploadPath = $kernel->getProjectDir() . "/public/upload/";
        // 如果有设置
        if (isset($_SERVER['APP_UPLOAD'])) {
            $uploadPath .= $kernel->getProjectDir() . '/public/' . $_SERVER['APP_UPLOAD'] . '/';
        }
        
        $uploadPath .= date('Y/m/d/');

        if (!is_dir($uploadPath)) {
            $fileSystem->mkdir($uploadPath, 0755);
        }

        $fileName = $fileInfo->getClientOriginalName();

        if ($fileSystem->exists($uploadPath . $fileName)) {
            $baseName = pathinfo($fileName, PATHINFO_BASENAME);
            $fileName = date('YmdHis.') . pathinfo($fileName, PATHINFO_EXTENSION);
        }

        $newFileInfo = $fileInfo->move($uploadPath, $fileName);
        $assetPackage = new Package(new EmptyVersionStrategy());

        $fileLink = $assetPackage->getUrl(str_replace($kernel->getProjectDir(), '', $newFileInfo->getRealPath()));

        return $this->json([
            'name' => $newFileInfo->getFilename(),
            'thumbUrl' => $fileLink,
            'url' => $fileLink,
        ]);
    }

    #[Route("/build/{file}", requirements: ['file' => '.*$'],  name: "build_files")]
    public function buildFile($file = "") {
        return new Response($file, headers: [
            'Content-Type' => 'text/javascript'
        ]);
    }
}
