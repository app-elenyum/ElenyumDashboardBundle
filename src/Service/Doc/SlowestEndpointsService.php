<?php

namespace Elenyum\Dashboard\Service\Doc;

class SlowestEndpointsService extends AbstractModuleService
{
    /**
     * @throws \Exception
     */
    public function getStats(): array
    {
        $endpoints = [];

        /** @var ControllerStatsLogObject $log */
        foreach ($this->getControllerStatsLog() as $log) {
            // Подсчитываем количество ошибок
            $duration = $log->getDuration();
            $endpoint = $log->getEndpoint();

            if (isset($endpoints[$endpoint])) {
                $endpoints[$endpoint]['duration'] = max($endpoints[$endpoint]['duration'], $duration);
            } else {
                // Иначе добавляем новый endpoint в статистику
                $endpoints[$endpoint] = ['duration' => $duration];
            }
        }

        uasort($endpoints, function ($a, $b) {
            return $b['duration'] - $a['duration'];
        });

        $values = [];
        foreach (array_slice($endpoints, 0, 10) as $controller => $data) {  // Выводим топ-5 медленных эндпоинтов
            $duration = $data['duration'];
            if ($duration <= 1000) {
                continue;
            }
            $formattedDuration = $this->formatDuration($duration);
            $values[$controller] = $formattedDuration;
        }

        return [
            'type' => 'list_card',
            'data' => [
                'name' => 'Slowest Endpoints',
                'values' => $values,
            ],
        ];
    }

    private function formatDuration(int $duration): string
    {
        if ($duration >= 1000) {
            return round($duration / 1000, 2).' sec';
        }

        return $duration.' ms';
    }
}
