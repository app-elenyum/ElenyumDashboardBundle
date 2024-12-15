<?php

namespace Elenyum\Dashboard\Service\Doc;

use DateTime;

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
                    'name' => 'Requests at week',
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
        return $this->getWeekDaysCentered();
    }

    /**
     * Возвращает массив сокращённых названий дней недели с текущим днём в центре.
     *
     * @param DateTime|null $currentDate Текущая дата. Если null, используется текущая дата.
     * @return array Массив сокращённых названий дней недели.
     */
    function getWeekDaysCentered(?DateTime $currentDate = null): array
    {
        // Используем текущую дату, если параметр не передан
        $currentDate = $currentDate ?: new DateTime();

        // Полный массив дней недели
        $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        // Определяем текущий день недели (1 - понедельник, 7 - воскресенье)
        $currentDayIndex = (int)$currentDate->format('N') - 1; // Приводим к индексу массива (0-based)

        // Формируем массив, где текущий день в центре
        $centeredWeekDays = [];
        $totalDays = count($weekDays);

        // Заполняем массив с учётом кругового сдвига
        for ($i = -3; $i <= 3; $i++) {
            $dayIndex = ($currentDayIndex + $i + $totalDays) % $totalDays; // Индекс с круговым смещением
            $centeredWeekDays[] = $weekDays[$dayIndex];
        }

        return $centeredWeekDays;
    }

    /**
     * @throws \Exception
     */
    private function getData(DateTime $referenceDate = null): array
    {
        // Опорная дата: текущая, если не передана
        $referenceDate = $referenceDate ?: new DateTime();

        // Получаем дни недели с текущим днём в центре
        $weekDayLabels = $this->getWeekDaysCentered($referenceDate);

        // Определяем начало и конец недели для фильтрации данных
        $startOfWeek = (clone $referenceDate)->modify('-3 days')->setTime(0, 0);
        $endOfWeek = (clone $startOfWeek)->modify('+3 days')->setTime(23, 59, 59);

        // Инициализируем массив с лейблами дней недели
        $result = array_fill_keys($weekDayLabels, 0);

        /** @var ControllerStatsLogObject $log */
        foreach ($this->getControllerStatsLog() as $log) {
            $logDate = $log->getTimestamp();

            // Фильтруем данные только для текущей недели
            if ($logDate >= $startOfWeek && $logDate <= $endOfWeek) {
                $logDayOfWeek = $logDate->format('D'); // Получаем сокращённый день недели
                if (isset($result[$logDayOfWeek])) {
                    $result[$logDayOfWeek] += 1;
                }
            }
        }

        // Возвращаем результат с сохранением порядка лейблов
        return array_values($result);
    }
}