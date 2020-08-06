<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('m6web_redis');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $root = $treeBuilder->getRootNode()->children();
        } else {
            $root = $treeBuilder->root('m6web_redis')->children();
        }

        $this->addServersSection($root);
        $this->addClientsSection($root);

        return $root;
    }

    private function addServersSection(ArrayNodeDefinition $rootNode)
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
                            ->scalarNode('database')->end()
                            ->scalarNode('scheme')->defaultValue('tcp')->end()
                            ->booleanNode('async_connect')->defaultValue(false)->end()
                            ->booleanNode('persistent')->defaultValue(false)->end()
                            ->floatNode('timeout')->defaultValue(10)->min(0)->end()
                            ->floatNode('read_write_timeout')->min(0)->end()
                            ->integerNode('reconnect')->defaultValue(0)->min(0)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('alias', false)
                    ->prototype('array')
                        ->children()
                            ->arrayNode('servers')->prototype('scalar')->end()->end()
                            ->booleanNode('compress')->defaultFalse()->end()
                            ->scalarNode('prefix')->defaultValue('')->end()
                            ->scalarNode('class')->defaultValue('M6Web\Bundle\RedisBundle\Redis\RedisClient')->end()
                            ->scalarNode('eventname')->defaultValue('redis.command')->end()
                            ->floatNode('timeout')->min(0)->end()
                            ->floatNode('read_write_timeout')->min(0)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
