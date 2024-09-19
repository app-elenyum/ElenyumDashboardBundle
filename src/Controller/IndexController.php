<?php

namespace Elenyum\Dashboard\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

class IndexController extends AbstractController
{
    public function __construct(
        private KernelInterface $kernel,
        private array $options = []
    ) {
    }


    public function __invoke(Request $request): Response
    {
        $file = $this->kernel->getProjectDir() . '/public/bundles/elenyumdashboard/index.html';

        $urlDefault = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]";

        $appConfig = [
            'options' => $this->options['options'] ?? [
                'prefix' => '/elenyum/dashboard'
            ],
            'dashboard' => $this->options['dashboard'] ?? [
                'enable' => true,
                'url' => $urlDefault,
                'endpoint' => '/elenyum/blocks'
            ],
            'user' => $this->options['user'] ?? [
                'enable' => true,
                'url' => $urlDefault,
                'endpoint' => '/elenyum/user'
            ],
            'editor' => $this->options['editor'] ?? [
                'enable' => true,
                'url' => $urlDefault,
                'endpoint' => '/elenyum/maker'
            ],
            'documentation' => $this->options['documentation'] ?? [
                'enable' => true,
                'url' => $urlDefault,
                'endpoint' => '/elenyum/doc'
            ],
        ];
        $html = file_get_contents($file);
        $html = str_replace('\'<!--%[AppConfig]%-->\'', json_encode($appConfig), $html);

        return new Response($html);
    }
}