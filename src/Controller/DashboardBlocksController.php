<?php

namespace Elenyum\Dashboard\Controller;

use Elenyum\Dashboard\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DashboardBlocksController extends AbstractController
{
    public function __construct(
        private DashboardService $service
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {

        return $this->json([
            'success' => true,
            'data' => [
                'blocks' => $this->service->getMetrics(),
            ],
        ]);
    }
}