<?php

namespace Elenyum\Dashboard\Service\Doc;

class DatabaseService extends AbstractModuleService
{
    public function getStats(): array
    {
        $doctrine = $this->parseConfig('packages/doctrine.yaml');

        $connections = $doctrine['doctrine']['dbal']['connections'];
        $mappings = $doctrine['doctrine']['orm']['entity_managers']['default']['mappings'];

        return [
            'type' => 'card',
            'data' => [
                'top' => [
                    'name' => 'Database',
                    'value' => count($connections),
                ],
                'bottom' => [
                    'name' => 'Total tables', // todo поправить счет количества таблиц
                    'value' => count($mappings),
                ],
            ],
        ];
    }
}