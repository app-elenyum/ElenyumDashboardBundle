<?php

namespace Elenyum\Dashboard\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elenyum_dashboard');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->booleanNode('enable')
                            ->info('define cache enable for dashboard')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('item_id')
                            ->info('define cache item id')
                            ->defaultValue('elenyum_dashboard')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('root')
                    ->info('Root info for create files')
                    ->children()
                        ->scalarNode('path')
                        ->info('target path for create module')
                        ->isRequired()
                    ->end()
                        ->scalarNode('namespace')
                        ->info('target namespace for create module')
                        ->defaultValue('Module')
                    ->end()
                        ->scalarNode('prefix')
                        ->info('define prefix item id')
                        ->defaultValue('Module')
                    ->end()
                ->end()

            ->end()
            ->arrayNode('options')
                ->info('Root info for create files')
                ->children()
                    ->scalarNode('prefix')
                        ->info('prefix for dashboard')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('dashboard')
                ->info('Root info for create files')
                ->children()
                    ->booleanNode('enable')
                        ->info('prefix for dashboard')
                        ->defaultValue(true)
                        ->isRequired()
                    ->end()
                    ->scalarNode('url')
                        ->info('url for dashboard')
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->info('endpoint for dashboard')
                        ->isRequired()
                    ->end()
                    ->arrayNode('login')
                        ->info('login for dashboard')
                        ->children()
                            ->booleanNode('enable')
                                ->info('prefix for dashboard')
                                ->defaultValue(true)
                                ->isRequired()
                            ->end()
                            ->scalarNode('endpoint')
                                ->info('endpoint for dashboard login')
                            ->end()
                            ->scalarNode('check')
                                ->info('check for dashboard login')
                            ->end()
                            ->arrayNode('groups')
                                ->info('groups for dashboard login')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('user')
                ->info('Root info for create files')
                ->children()
                    ->booleanNode('enable')
                        ->info('prefix for user')
                        ->defaultValue(true)
                        ->isRequired()
                    ->end()
                    ->scalarNode('url')
                        ->info('url for user')
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->info('endpoint for user')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('editor')
                ->info('Root info for create files')
                ->children()
                    ->booleanNode('enable')
                        ->info('prefix for editor')
                        ->defaultValue(true)
                        ->isRequired()
                    ->end()
                    ->scalarNode('url')
                        ->info('url for editor')
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->info('endpoint for editor')
                        ->isRequired()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('documentation')
                ->info('Root info for create files')
                ->children()
                    ->booleanNode('enable')
                        ->info('prefix for documentation')
                        ->defaultValue(true)
                        ->isRequired()
                    ->end()
                    ->scalarNode('url')
                        ->info('url for documentation')
                        ->isRequired()
                    ->end()
                    ->scalarNode('endpoint')
                        ->info('endpoint for documentation')
                        ->isRequired()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
