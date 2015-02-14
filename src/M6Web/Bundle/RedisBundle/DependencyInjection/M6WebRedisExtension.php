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
        foreach ($clients as $alias => $clientConfig) {
            switch ($clientConfig['type']){
                case 'cache':
                    $this->loadCacheClient($container, $alias, $clientConfig, $servers);
                    break;
                case 'db':
                    $this->loadDbClient($container, $alias, $clientConfig, $servers);
                    break;
                case 'multi':
                    $this->loadMultiClient($container, $alias, $clientConfig, $servers);
                    break;
                default:
                    throw new InvalidConfigurationException("Invalid client type");
            }
        }

        if ($container->getParameter('kernel.debug')) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('data_collector.yml');
        }
    }


    /**
     * load a DB client
     *
     * @param ContainerInterface $container The container
     * @param string             $alias     Alias of the client
     * @param array              $config    Base config of the client
     * @param array              $servers   List of available servers as describe in the config file
     */
    protected function loadDbClient($container, $alias, array $config, array $servers)
    {
        $this->loadClient($container, $alias, $config, $servers, 'db');
    }

    /**
     * load a Multi client
     *
     * @param ContainerInterface $container The container
     * @param string             $alias     Alias of the client
     * @param array              $config    Base config of the client
     * @param array              $servers   List of available servers as describe in the config file
     */
    protected function loadMultiClient($container, $alias, array $config, array $servers)
    {
        $this->loadClient($container, $alias, $config, $servers, 'multi');
    }

    /**
     * Load a client configuration as a service in the container. A client can use multiple servers
     * @param ContainerInterface $container The container
     * @param string             $alias     Alias of the client
     * @param array              $config    Base config of the client
     * @param array              $servers   List of available servers as describe in the config file
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return void
     */
    protected function loadCacheClient($container, $alias, array $config, array $servers)
    {
        $this->loadClient($container, $alias, $config, $servers, 'cache');
    }

    /**
     * Load a dbclient configuration as a service in the container. A client can use multiple servers
     * @param ContainerInterface $container The container
     * @param string             $alias     Alias of the client
     * @param array              $config    Base config of the client
     * @param array              $servers   List of available servers as describe in the config file
     * @param string             $type      db or multi
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return void
     */
    protected function loadClient($container, $alias, array $config, array $servers, $type = 'cache')
    {
        $configuration = array(
            'timeout'        => $config['timeout'],
            'server_config'  => array()
        );

        if (array_key_exists('readwritetimeout', $config)) {
            $configuration['read_write_timeout'] = $config['readwritetimeout'];
        }

        if ('cache' === $type) {
            // check namespace
            if (!isset($config['namespace'])) {
                throw new InvalidConfigurationException("namespace Parameter for M6Redis cache server is required");
            }
            $configuration['namespace'] = $config['namespace'];
            $configuration['compress']  = $config['compress'];
        }

        $configuration['server_config'] = $this->getServers($servers, $config['servers'], $alias);

        if (count($configuration['server_config']) === 0) {
            throw new InvalidConfigurationException(sprintf("no server found for M6Redis client %s", $alias));
        }

        switch ($type) {
            case 'cache':
                $redisCacheId = sprintf('m6_redis.cache.%s', $alias);
                $container
                    ->register($redisCacheId, 'M6Web\Component\Redis\Cache')
                    ->addArgument($configuration);
                $serviceId  = ($alias == 'default') ? 'm6_redis' : 'm6_redis.'.$alias;
                $definition = new Definition($config['class']);
                $definition->addArgument(new Reference($redisCacheId));
                break;
            case 'db':
                $serviceId  = ($alias == 'default') ? 'm6_dbredis' : 'm6_dbredis.'.$alias;
                $definition = new Definition('M6Web\Component\Redis\DB');
                $definition->addArgument($configuration);
                break;
            case 'multi':
                $serviceId  = ($alias == 'default') ? 'm6_multiredis' : 'm6_multiredis.'.$alias;
                $definition = new Definition('M6Web\Component\Redis\Multi');
                $definition->addArgument($configuration);
                break;
            default:
                throw new InvalidConfigurationException("Invalid client type");
        }

        $definition->setScope(ContainerInterface::SCOPE_CONTAINER);
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

    /**
     * @param array  $allServers array of all servers available
     * @param array  $servers    array of servers defined for a client
     * @param string $alias      alias of the client
     *
     * @return array
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function getServers(array $allServers, array $servers, $alias)
    {
        $serverToAdd = array();
        $toReturn    = array();

        foreach ($servers as $serverAlias) {
            // wildcard detected
            if ((false !== strpos($serverAlias, '*')) or (false !== strpos($serverAlias, '?'))) {
                $serverFound = 0;
                foreach ($allServers as $serverName => $server) {
                    // serverName match the wildcard
                    if (fnmatch($serverAlias, $serverName)) {
                        $serverToAdd[$serverName] = $server;
                        $serverFound++;
                    }
                }
                // no server found
                if (0 === $serverFound) {
                    throw new InvalidConfigurationException("M6Redis client $alias used server $serverAlias which doesnt match to any servers");
                }
                // concrete server
            } else {
                if (!isset($allServers[$serverAlias])) {
                    throw new InvalidConfigurationException("M6Redis client $alias used server $serverAlias which is not defined in the servers section");
                } else {
                    $serverToAdd[$serverAlias] = $allServers[$serverAlias];
                }
            }
            foreach ($serverToAdd as $serverName => $server) {
                $toReturn[$serverName] = array('ip' => $server['ip'], 'port' => $server['port']);
            }
        }

        return $toReturn;
    }
}
