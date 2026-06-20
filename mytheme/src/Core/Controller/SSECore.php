<?php

namespace App\Core\Controller;

use App\Core\EventSource;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class SSECore
{
  private EventSource $event;
  private int $max = 10;

  public function __construct(EventSource $event)
  {
    $this->event = $event;
  }

  public static function createDataArray(string $event, mixed $data, array $option = []): array
  {
    return [
      'event' => $event,
      'data' => is_array($data) || is_object($data) ? json_encode($data) : $data,
      ...$option,
    ];
  }

  public static function createResponse(callable $callback, string $type = 'new', int|float $interval = 2, int $retry = 10000, int $max = 10): StreamedResponse
  {
    ini_set('output_buffering', 'off');
    ini_set('zlib.output_compression', 0);
    ini_set(
      'max_execution_time',
      60 * $max
    );
    set_time_limit(60 * $max);
    ignore_user_abort(null);
    $frist = -5;

    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('Connection', 'keep-alive');
    $response->headers->set('X-Accel-Buffering', 'no');  // Nginx: unbuffered responses suitable for Comet and HTTP streaming applications

    remove_action('template_redirect', 'wp_finalize_template_enhancement_output_buffer', 20);
    remove_action('wp_footer', 'wp_admin_bar_render', 100);  // 如果有其他输出阻塞也移除

    
    function contentOutPut($callback, $id, $retry) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $data = call_user_func($callback);
        $content = [];
        if ($data) {
            if ($data['event'] != 'close') {
                $content[] = 'id: ' . $id . "\n";  // 填充数据
                $content[] =  'retry: ' . $retry . "\n";
                $content[] =  'event: ' . $data['event'] . "\n";  // 填充数据
                $content[] =  'data: ' . $data['data'] . "\n\n\n\n";  // 填充数据
            } else {
                return false;
            }
        }
	        
        echo implode('', $content);
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        return true;
    }
    
    
    $response->setCallback(function () use ($callback, $type, $interval, $retry, $max, &$frist) {
      $id = md5(uniqid());
      $endTime = $max * 60;
      $start = time();
      echo 'ping: connected' . PHP_EOL . PHP_EOL;
      flush();
      
      while (!connection_aborted() && (time() - $start) <= $endTime) {

        if (connection_aborted()) {
          break;
        }

        if (time() - $start > $endTime) {
          break;
        }


        $data = contentOutPut($callback, $id, $retry);
        $content = [];
        if (!$data) {
            break;
        } else {
          //echo 'ping: ' . date('Y-m-d H:i:s') . "\n\n";
        }
        
        sleep($interval);
      }



      //ob_end_flush();
    });
    return $response;
  }

  /**
   * 开始任务
   *
   * @param integer $interval
   * @return void
   */
  public function start(int|float $interval = 3, int $max = 10): void
  {
    ini_set(
      'max_execution_time',
      60 * $max
    );
    set_time_limit(60 * $max);
    ignore_user_abort(null);

    while (connection_aborted() === 0) {
      $this->event->fill();  // 填充数据
      if (ob_get_level() > 0) {
        ob_flush();
      }
      flush();
      usleep($interval * 1000);
    }

    register_shutdown_function(function () {
      ob_end_flush();
    });
  }
}
