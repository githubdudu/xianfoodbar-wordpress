<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoteOrderExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private bool $debug
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/remote/getdata')) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $this->logger->error($exception->getTraceAsString(), ['exception' => $exception]);

        $event->setResponse(new JsonResponse([
            'status' => $statusCode,
            'message' => $this->debug ? $exception->getMessage() : 'Internal Server Error',
            'trace' => $this->debug ? explode("\n", $exception->getTraceAsString()) : null,
        ], $statusCode));
    }
}
