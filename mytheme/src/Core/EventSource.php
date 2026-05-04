<?php

namespace App\Core;

class EventSource
{
    private string $id = '';
    private ?string $data = '';
    private string $comment = '';
    private string $initialEvent = '';
    private string $event = '';
    private string $retry = '';
    private $callback;

    public function create(callable $callback, string $event, int $retry = 20000)
    {
        $this->callback = $callback;
        $this->initialEvent = $this->event = $event;
        $this->retry = $retry;

        return $this;
    }

    public function fill()
    {
        $this->event = $this->initialEvent;
        try {
            $result = call_user_func($this->callback);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        if ($result === false) {
            $this->id = '';
            $this->data = '';
            // $this->comment = 'no data';
        } else {
            if (isset($result['event'])) {
                $this->event = $result['event'];
            }

            if ($this->event == 'close') {
                echo $this->event;
            }

            echo $result['file'] ?? '';

            $this->id = $result['id'] ?? str_replace('.', '', uniqid('', true));
            $this->data = $result['data'] ?? $result;
            $this->comment = $result['comment'] ?? '';
        }

        if (!$this->data) {
            return null;
        }

        echo $this;

        if ($this->event == 'close') {
            exit;
        }
    }

    public function __toString()
    {
        $message = [];

        // if ($this->data == '') {
        //     return '';
        // }
        $event = [];

        if ($this->comment) {
            $event[] = sprintf(': %s', $this->comment);
        }

        if ($this->id) {
            $event[] = sprintf('id: %s', $this->id);
        }

        if ($this->retry > 0) {
            $event[] = sprintf('retry: %s', $this->retry);
        }

        if ($this->event) {
            $event[] = sprintf('event: %s', $this->event);
        }

        if ($this->data) {
            $event[] = sprintf('data: %s', $this->data);
        }

        return implode("\n", $event) . "\n\n";
    }
}
