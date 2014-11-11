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

        $this->addServersSection($rootNode);
        $this->addClientsSection($rootNode);

        return $treeBuilder;
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
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue(6379)->end()
                            ->integerNode('database')->defaultValue(0)->end()
                            ->scalarNode('scheme')->defaultValue('tcp')->end()
                            ->booleanNode('connection_async')->defaultValue('false')->end()
                            ->booleanNode('connection_persistent')->defaultValue('false')->end()
                            ->floatNode('timeout')->defaultValue(10)->min(0)->end()
                            ->floatNode('read-write-timeout')->min(0)->end()
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
                            ->scalarNode('class')->defaultValue('M6Web\Bundle\RedisBundle\Redis\Redis')->end()
                            ->scalarNode('eventnames')->defaultValue('redis.command')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
