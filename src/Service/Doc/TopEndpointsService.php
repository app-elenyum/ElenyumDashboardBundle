<?php

namespace Elenyum\Dashboard\Service\Doc;

class TopEndpointsService extends AbstractModuleService
{
    /**
     * @throws \Exception
     */
    public function getStats(): array
    {
        $endpoints = [];

        /** @var ControllerStatsLogObject $log */
        foreach ($this->getControllerStatsLog() as $log) {
            $endpoint = $log->getEndpoint();
            $method = $log->getMethod();

            if (isset($endpoints[$endpoint])) {
                $endpoints[$endpoint]++;
            } else {
                $endpoints[$endpoint] = 1;
            }
        }

        // Сортируем endpoints по количеству вызовов в порядке убывания
        arsort($endpoints);

        // Ограничиваем результат до 10 записей
        $topEndpoints = array_slice($endpoints, 0, 10, true);

        // Формируем массив для вывода в требуемом формате
        $values = [];
        foreach ($topEndpoints as $endpoint => $count) {
            $values[$method.':'.$endpoint] = $count.' calls';
        }

        return [
            'type' => 'list_card',
            'data' => [
                'name' => 'Top Endpoints at 30 days',
                'values' => $values,
            ],
        ];
    }
}
