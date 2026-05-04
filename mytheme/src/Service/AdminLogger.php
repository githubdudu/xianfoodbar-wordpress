<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class AdminLogger
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function error(string $message = '', array $context = []) {
        $this->logger->error($message, $context);
    }

    public function debug(string $message = '', array $context = []) {
        $this->logger->debug($message, $context);
    }

    public function notice(string $message = '', array $context = []) {
        $this->logger->notice($message, $context);
    }
}