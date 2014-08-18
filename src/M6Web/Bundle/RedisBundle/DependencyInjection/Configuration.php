<?php

namespace M6Web\Bundle\RedisBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('m6_redis');
        $rootNode->children()
            ->booleanNode('disable_data_collector')->defaultValue(false)->end()
        ->end();
        $this->addServersSection($rootNode);
        $this->addClientsSection($rootNode);


        return $treeBuilder;
    }


    private function addDbResetterSection($rootNodeDb)
    {
        $rootNodeDb
            ->children()
                ->scalarNode('cache_resetter')->defaultValue(null)->end()
            ->end();

    }

    private function addServersSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('servers')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('alias', false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('ip')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue(6379)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addClientsSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('alias', false)
                    ->prototype('array')
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('namespace')->defaultNull()->end()
                            ->floatNode('timeout')
                                ->defaultValue(10)
                                ->min(0)
                            ->end()
                            ->enumNode('type')
                                ->values(['cache', 'db', 'multi'])
                                ->defaultValue('cache')
                            ->end()
                            ->booleanNode('compress')->defaultFalse()->end()
                            ->scalarNode('cache_resetter')->end()
                            ->scalarNode('class')->defaultValue('M6Web\Bundle\RedisBundle\Redis\Redis')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
