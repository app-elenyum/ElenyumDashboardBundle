<?php

namespace Elenyum\Dashboard\EventListener;

use Elenyum\Dashboard\Attribute\StatCountRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;


class StatCountRequestListener implements EventSubscriberInterface
{
    private ?int $startTime = null;
    private int $endTime;

    public function __construct(
        private LoggerInterface $elenyumControllerStatsLogger
    ) {
    }


    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelController', 20],
            KernelEvents::RESPONSE => ['onKernelResponse', 20],
            KernelEvents::EXCEPTION => ['onKernelException', 20]
        ];
    }

    public function onKernelController(ControllerArgumentsEvent $event)
    {
        /** @var StatCountRequest[] $attributes */
        if (!\is_array($event->getAttributes()[StatCountRequest::class] ?? null)) {
            return;
        }

        $this->startTime = microtime(true);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        // Проверяем наличие атрибута StatCountRequest
        if ($this->startTime !== null) {
            // Сохраняем время окончания выполнения контроллера
            $this->endTime = microtime(true);

            // Вычисляем длительность выполнения контроллера
            $duration = $this->endTime - $this->startTime;

            // Получаем информацию о контроллере
            $controller = $request->attributes->get('_controller');
            $timestamp = date('Y-m-d H:i:s');
            $endpoint = $request->getPathInfo();

            // Логируем информацию в файл
            $this->logRequestWidthMonologData($timestamp, $controller, $endpoint, $duration);
        }
    }

    private function logRequestWidthMonologData($timestamp, $controller, $endpoint, $duration)
    {
        // Логируем информацию
        $this->elenyumControllerStatsLogger->info('Controller executed.', [
            'timestamp' => $timestamp,
            'endpoint' => $endpoint,
            'controller' => $controller,
            'duration' => $duration,
        ]);
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if ($this->startTime !== null) {
            $this->endTime = microtime(true);
            $duration = $this->endTime - $this->startTime;

            $controller = $request->attributes->get('_controller');
            $timestamp = date('Y-m-d H:i:s');
            $endpoint = $request->getPathInfo();
            $errorMessage = $exception->getMessage();
            $errorCode = $exception->getCode();

            // Логируем информацию об ошибке
            $this->elenyumControllerStatsLogger->error('Controller execution failed.', [
                'timestamp' => $timestamp,
                'endpoint' => $endpoint,
                'controller' => $controller,
                'duration' => $duration,
                'error_message' => $errorMessage,
                'error_code' => $errorCode,
            ]);
        }
    }
}