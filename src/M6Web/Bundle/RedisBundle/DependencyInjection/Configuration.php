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
        $this->addCacheResetterSection($rootNode);
        $this->addServersSection($rootNode);
        $this->addClientsSection($rootNode);

        $rootNodeDb = $treeBuilder->root('m6_dbredis');
        $this->addDbResetterSection($rootNodeDb);
        $this->addServersSection($rootNodeDb);
        $this->addClientsSection($rootNodeDb);

        return $treeBuilder;
    }

    private function addCacheResetterSection($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('cache_resetter')->defaultValue(null)->end()
                //->scalarNode('concurrent_max')->defaultValue(0)->end()
                //->scalarNode('ttl_lock')->defaultValue(10)->end()
                //->scalarNode('ttl_key_value_multiplier')->defaultValue(3)->end()
            ->end();

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
                            ->scalarNode('timeout')->defaultValue(100)->end()
                            ->scalarNode('type')->defaultValue('cache')->end()
                            ->scalarNode('compress')->defaultValue(false)->end()
                            //->scalarNode('concurrent_max')->defaultValue(5)->end()
                            //->scalarNode('ttl_lock')->defaultValue(10)->end()
                            //->scalarNode('ttl_key_value_multiplier')->defaultValue(3)->end()
                            ->scalarNode('cache_resetter')->end()
                            ->scalarNode('class')->defaultValue('M6Web\Bundle\RedisBundle\Redis\Redis')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
