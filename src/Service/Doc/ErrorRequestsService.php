<?php

namespace Elenyum\Dashboard\Service\Doc;

class ErrorRequestsService extends AbstractModuleService
{
    /**
     * @throws \Exception
     */
    public function getStats(): array
    {
        $errorCount = 0;
        $totalCount = 0;

        /** @var ControllerStatsLogObject $log */
        foreach ($this->getControllerStatsLog() as $log) {
            // Подсчитываем количество ошибок
            if ($log->getStatus() === ControllerStatsLogObject::STATUS_ERROR) {
                $errorCount++;
            }

            $totalCount++;
        }

        return [
            'type' => 'card',
            'data' => [
                'top' => [
                    'name' => 'Error requests at 30 days',
                    'value' => $errorCount,
                ],
                'bottom' => [
                    'name' => 'Total requests at 30 days',
                    'value' => $totalCount,
                ],
            ],
        ];
    }
}