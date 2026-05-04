<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use DateInterval;

class AdminMessage
{
    private array $message = [];
    private FilesystemAdapter $cache;
    private string $cacheFile;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(private readonly KernelInterface $kernel)
    {
        $this->cacheFile = $this->kernel->getCacheDir() . '/message';
    }

    /**
     * 读取文件到message里
     *
     * @return void
     */
    private function read(): void
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->cacheFile)) {
            $fileInfo = new SplFileInfo($this->cacheFile, '', '');
            $message = json_decode($fileInfo->getContents(), true) ?: [];
            $this->message = is_array($message) ? $message : [];
        }
    }

    /**
     * 写入message的内容覆盖文件
     *
     * @return void
     */
    private function save(): void
    {
        $fileInfo = new SplFileInfo($this->cacheFile, '', '');
        $write = $fileInfo->openFile('w');
        $write->fwrite(json_encode($this->message, JSON_NUMERIC_CHECK));
    }

    /**
     * 添加一条消息
     *
     * @param string $title
     * @param string $content
     * @return void
     */
    public function addMessage(string $title, string $content = '', bool $playMusic = false, ?string $musicFile = null)
    {
        $this->message[] = [
            'event' => 'message',
            'data' => json_encode([
                'title' => $title,
                'content' => $content,
                'play' => $playMusic,
                'voice' => $musicFile ?? ''
            ]),
        ];

        // $this->message[] = [
        //     'event' => 'message',
        //     'data' => json_encode([
        //         'title' => $title,
        //         'content' => $content,
        //         'play' => $playMusic,
        //         'voice' => $musicFile ?? ""
        //     ]),
        // ];

        $this->save();
    }

    /**
     * 读取一条消息
     *
     * @return array
     */
    public function getMessage(): array
    {
        if (empty($this->message)) {
            $this->read();
        }

        $last = array_shift($this->message);
        $this->save();

        return $last ?? [];
    }

    /**
     * 读取所有消息
     *
     * @return array
     */
    public function getAllMessage(): array
    {
        if (empty($this->message)) {
            $this->read();
        }

        $last = $this->message;
        $this->message = [];
        $this->save();

        return $last ?? [];
    }
}
