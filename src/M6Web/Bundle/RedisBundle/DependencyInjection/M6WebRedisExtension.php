<?php

namespace M6Web\Bundle\RedisBundle\DependencyInjection;

use M6Web\Component\Redis\Cache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebRedisExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $servers = isset($config['servers']) ? $config['servers'] : array();
        $clients = isset($config['clients']) ? $config['clients'] : array();
        $cacheResetterService = $config['cache_resetter'];
        foreach ($clients as $alias => $config) {
            if ($config['type'] == 'db') {
                $this->loadDbClient($container, $alias, $config, $servers);
            } else {
                $this->loadClient($container, $alias, $config, $servers, $cacheResetterService);
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('data_collector.yml');
    }

    /**
     * Load a client configuration as a service in the container. A client can use multiple servers
     * @param ContainerInterface $container            The container
     * @param string             $alias                Alias of the client
     * @param array              $config               Base config of the client
     * @param array              $servers              List of available servers as describe in the config file
     * @param object             $cacheResetterService Set the cache resetter service to use
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return void
     */
    protected function loadClient($container, $alias, array $config, array $servers, $cacheResetterService = null)
    {
        $configuration = array(
            'namespace'      => $config['namespace'],
            'timeout'        => $config['timeout'],
            'compress'       => $config['compress'],
            'server_config'  => array()
        );

        if (!isset($config['namespace'])) {
            throw new InvalidConfigurationException("namespace Parameter for M6Redis server is required");
        }

        foreach ($config['servers'] as $serverAlias) {
            if (!isset($servers[$serverAlias])) {
                throw new InvalidConfigurationException("M6Redis client $alias used server $serverAlias which is not defined in the servers section");
            } else {
                $serverConfig = $servers[$serverAlias];
                $configuration['server_config'][$serverAlias] = array('ip' => $serverConfig['ip'], 'port' => $serverConfig['port']);
            }
        }

        if (count($configuration['server_config']) == 0) {
            throw new InvalidConfigurationException(sprintf("no server configured for %s client", $alias));
        }

        $redisCacheId = sprintf('m6_redis.cache.%s', $alias);
        $container
            ->register($redisCacheId, 'M6Web\Component\Redis\Cache')
            ->addArgument($configuration);

        $serviceId  = ($alias == 'default') ? 'm6_redis' : 'm6_redis.'.$alias;
        $definition = new Definition($config['class']);

        $definition->setScope(ContainerInterface::SCOPE_CONTAINER);
        $definition->addArgument(new Reference($redisCacheId));
        $definition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher'), 'M6Web\Bundle\RedisBundle\EventDispatcher\RedisEvent'));
        /*$definition->addMethodCall('setConcurrentMax', array($config['concurrent_max']));
        $definition->addMethodCall('setTtlKeyValueMultiplier', array($config['ttl_key_value_multiplier']));
        $definition->addMethodCall('setTtlLock', array($config['ttl_lock']));*/



        if (array_key_exists('cache_resetter', $config)) {
            $definition->addMethodCall('setCacheResetter', array(new Reference($config['cache_resetter'])));
        }
        $container->setDefinition($serviceId, $definition);
    }

    /**
     * Load a dbclient configuration as a service in the container. A client can use multiple servers
     * @param ContainerInterface $container The container
     * @param string             $alias     Alias of the client
     * @param array              $config    Base config of the client
     * @param array              $servers   List of available servers as describe in the config file
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return void
     */
    protected function loadDbClient($container, $alias, array $config, array $servers)
    {
        $configuration = array(
            'timeout'        => $config['timeout'],
            'server_config'  => array()
        );

        foreach ($config['servers'] as $serverAlias) {
            if (!isset($servers[$serverAlias])) {
                throw new InvalidConfigurationException("M6Redis client $alias used server $serverAlias which is not defined in the servers section");
            } else {
                $serverConfig = $servers[$serverAlias];
                $configuration['server_config'][$serverAlias] = array('ip' => $serverConfig['ip'], 'port' => $serverConfig['port']);
            }
        }

        if (count($configuration['server_config']) != 1) {
            throw new InvalidConfigurationException(sprintf("M6Redis client %s used is a db client and can't have %s server configured", $alias, count($configuration['server_config'])));
        }

        $serviceId  = ($alias == 'default') ? 'm6_dbredis' : 'm6_dbredis.'.$alias;
        $definition = new Definition('M6Web\Component\Redis\DB');

        $definition->setScope(ContainerInterface::SCOPE_CONTAINER);
        $definition->addArgument($configuration);
        $definition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher'), 'M6Web\Bundle\RedisBundle\EventDispatcher\RedisEvent'));

        $container->setDefinition($serviceId, $definition);
    }


    /**
     * select an alias for the extension
     *
     * trick allowing bypassing the Bundle::getContainerExtension check on getAlias
     * not very clean, to investigate
     *
     * @return string
     */
    public function getAlias()
    {
        return 'm6_redis';
    }
}
