<?php

namespace Elenyum\Dashboard\Service\Doc;

class ResponseTimeService extends AbstractModuleService
{
    /**
     * @throws \Exception
     */
    public function getStats(): array
    {
        $durations = [];

        foreach ($this->getControllerStatsLog() as $log) {
            $durations[] = $log->getDuration();
        }

        if (empty($durations)) {
            return $this->getDefaultStats();
        }

        // Подсчитываем среднее время отклика
        $average = array_sum($durations) / count($durations);

        // Подсчитываем 95-й перцентиль
        sort($durations);
        $percentileIndex = (int)ceil(0.95 * count($durations)) - 1;
        $percentile95th = $durations[$percentileIndex];

        return [
            'type' => 'card',
            'data' => [
                'top' => [
                    'name' => 'Average Response Time',
                    'value' => round($average).' ms',
                ],
                'bottom' => [
                    'name' => '95th Percentile Response Time',
                    'value' => $percentile95th.' ms',
                ],
            ],
        ];
    }

    private function getDefaultStats(): array
    {
        // Возвращаем стандартные значения, если данных нет
        return [
            'type' => 'card',
            'data' => [
                'top' => [
                    'name' => 'Average Response Time',
                    'value' => '0 ms',
                ],
                'bottom' => [
                    'name' => '95th Percentile Response Time',
                    'value' => '0 ms',
                ],
            ],
        ];
    }
}