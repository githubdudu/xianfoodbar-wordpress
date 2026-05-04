<?php

namespace App\Core\Controller;

use App\Service\AdminRequests;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;

abstract class RESTController extends AbstractController
{

  private array $dataJson = [];
  private array $headerJson = [
    // 'Content-Type' => 'application/json'
  ];

  /**
   * Router
   *
   * @var RouterInterface
   */
  public RouterInterface $router;

  /**
   * Request
   * @var Request
   */
  public Request $request;


  public function __construct(RouterInterface $router,  RequestStack $req, KernelInterface $app, AdminRequests $httpClient)
  {
    $this->router = $router;
    $this->request = $req->getCurrentRequest();

    if ($this->request->getContentType() === 'json' || $this->request->getContentType() === 'txt') {
      $json = json_decode($this->request->getContent(), true);
      if (is_array($json)) {
        $this->request->request->__construct($json);
      }
    }

    $this->initialize();
  }

  protected function initialize() {}
  /**
   * 添加内容
   *
   * @param string $key 键
   * @param mixed $value 值
   * @return void
   */
  public function addJsonData(string $key, $value = null)
  {
    $this->dataJson[$key] = $value;
  }

  /**
   * 添加头部信息
   *
   * @param string $key 键
   * @param mixed $value 值
   * @return void
   */
  public function addJsonHeader(string $key, $value = null)
  {
    $this->headerJson[$key] = $value;
  }

  /**
   * 返回Json
   *
   * @param string $name 减脂
   * @param array $data
   * @param string $message 提醒的内容
   * @param integer $code
   * @param array $header
   * @param array $option
   * @return Response
   */
  public function sendJson(string $message = "", int $code = 200, array $option = [], array $header = []): Response
  {
    // 添加状态码信息
    $this->addJsonData('status', $code);
    // 添加扩展信息
    $this->addJsonData('_option', $option);
    // 添加内容
    $this->addJsonData('message', $message);
    // 发送
    return $this->json($this->dataJson, $code, array_merge($this->headerJson, $header));
  }

  // protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
  // {
  //     if (is_object($data) || (is_array($data) && isset($data[0]) && is_object($data[0]))) {
  //         if (!is_array($data)) {
  //             $ref = new ReflectionClass($data);
  //             if (count($ref->getAttributes(Entity::class)) > 0) {
  //                 $data = new DataSource($data);
  //             }
  //         } else {
  //             foreach ($data as $key => $v) {
  //                 $ref = new ReflectionClass($v);
  //                 if (count($ref->getAttributes(Entity::class)) > 0) {
  //                     $data[$key] = new DataSource($v);
  //                 }
  //             }
  //         }
  //     }

  //     return parent::json($data, $status, $headers, $context);
  // }
}
