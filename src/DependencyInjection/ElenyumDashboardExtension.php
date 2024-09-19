<?php

namespace Elenyum\Dashboard\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

class ElenyumDashboardExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        // add to container parameters

        $container->setParameter('elenyum_dashboard.config', $config);
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2). '/config' )
        );

        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $config = Yaml::parse(file_get_contents(dirname(__DIR__, 2). '/config/monolog.yaml'));
        $container->prependExtensionConfig('monolog', $config['monolog']);
    }
}