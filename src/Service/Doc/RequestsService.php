<?php

namespace Elenyum\Dashboard\Service\Doc;

class RequestsService extends AbstractModuleService
{
    /**
     * @throws \Exception
     */
    public function getStats(): array
    {
        $data = $this->getData();

        return [
            'type' => 'graph_card',
            'data' => [
                'header' => [
                    'name' => 'Requests',
                    'value' => array_sum($data),
                ],

                'graph' => [
                    'labels' => $this->getLabels(),
                    'datasets' => [
                        [
                            'label' => 'Dataset',
                            'data' => $data,
                            'fill' => false,
                            'borderColor' => 'rgba(6, 182, 212, 0.5)',
                            'tension' => 0.4,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getLabels(): array
    {
        return [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
    }

    /**
     * @throws \Exception
     */
    private function getData(): array
    {
        $result = array_fill(1, 12, 0);

        /** @var ControllerStatsLogObject $log */
        foreach ($this->getControllerStatsLog() as $log) {
            $result[$log->getTimestamp()->format('n')] += 1;
        }
        ksort($result);

        return array_values($result);
    }
}