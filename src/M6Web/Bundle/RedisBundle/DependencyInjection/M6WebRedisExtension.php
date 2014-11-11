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
        $servers = isset($config['servers']) ? $config['servers'] : [];
        $clients = isset($config['clients']) ? $config['clients'] : [];
        foreach ($clients as $alias => $clientConfig) {
            $this->loadClient($container, $alias, $config, $servers);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('data_collector.yml');
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
    protected function loadClient($container, $alias, array $config, array $servers)
    {
        $options = [];

        if (array_key_exists('namespace', $config)) {
            $options['prefix'] = $config['namespace'];
        }

        $servers = $this->getServers($servers, $config['servers'], $alias);

        if (count($servers) === 0) {
            throw new InvalidConfigurationException(sprintf("no server found for client %s", $alias));
        }

        // make internal predis service
        $redisCacheId = sprintf('m6web_redis.predis.%s', $alias);
        $container
            ->register($redisCacheId, 'Predis\Client')
            ->addArgument($servers)
            ->addArgument($options);

        $serviceId  = ($alias == 'default') ? 'm6web_redis' : 'm6web_redis.'.$alias;


        $definition = new Definition($config['class']);
        $definition->addArgument(new Reference($redisCacheId));
        $definition->setScope(ContainerInterface::SCOPE_CONTAINER);
        $definition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher')));
        $definition->addMethodCall('setEventNames', explode('|', $config['eventnames']));

        $container->setDefinition($serviceId, $definition);
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
        $serverToAdd = [];
        $toReturn    = [];

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
                    throw new InvalidConfigurationException("M6WebRedis client $alias used server $serverAlias which doesnt match to any servers configured");
                }
                // concrete server
            } else {
                if (!isset($allServers[$serverAlias])) {
                    throw new InvalidConfigurationException("M6WebRedis client $alias used server $serverAlias which is not defined in the servers section");
                } else {
                    $serverToAdd[$serverAlias] = $allServers[$serverAlias];
                }
            }
            foreach ($serverToAdd as $alias => $server) {
                //format the array according to predis client need
                $toReturn[] = $server + array('alias', $alias);
            }
        }

        return $toReturn;
    }
}
