<?php

namespace Elenyum\Dashboard\Service;

use Countable;
use Elenyum\Dashboard\Service\Doc\DocInterface;
use Exception;

class DashboardService
{
    public function __construct(
        public Countable $cards,
    ) {
    }

    /**
     * @return array
     */
    public function getMetrics(): array
    {

        $result = [];
        try {

        foreach ($this->cards as $card) {
            if ($card instanceof DocInterface) {
                $result[] = $card->getStats();
            }
        }

        } catch (Exception $e) {
            dd([$e->getMessage(), $e->getFile(), $e->getLine()]);
        }

        return $result;
    }
}