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
        //$servers = isset($config['servers']) ? $config['servers'] : [];
        $clients = isset($config['clients']) ? $config['clients'] : [];
        foreach ($clients as $clientAlias => $clientConfig) {
            $this->loadClient($container, $clientAlias, $config);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('data_collector.yml');
    }

    /**
     * Load a dbclient configuration as a service in the container. A client can use multiple servers
     * @param ContainerInterface $container   The container
     * @param string             $clientAlias Alias of the client
     * @param array              $config      global config
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return void
     */
    protected function loadClient($container, $clientAlias, array $config)
    {
        $options = [];

        $clientConfig = $config['clients'][$clientAlias];

        if (array_key_exists('namespace', $clientConfig)) {
            $options['prefix'] = $clientConfig['namespace'];
        }

        $servers = $this->getServers($config['servers'], $clientConfig['servers'], $clientAlias);

        if (count($servers) === 0) {
            throw new InvalidConfigurationException(sprintf("no server found for client %s", $clientAlias));
        }

        // make internal predis service
        $redisCacheId = sprintf('m6_redis.predis.%s', $clientAlias);
        $container
            ->register($redisCacheId, 'Predis\Client')
            ->addArgument($servers)
            ->addArgument($options);

        $serviceId  = ($clientAlias == 'default') ? 'm6_redis' : 'm6_redis.'.$clientAlias;


        $definition = new Definition($clientConfig['class']);
        $definition->addArgument(new Reference($redisCacheId));
        $definition->setScope(ContainerInterface::SCOPE_CONTAINER);
        $definition->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);
        $definition->addMethodCall('setEventName', [$clientConfig['eventname']]);

        $container->setDefinition($serviceId, $definition);
    }

    /**
     * @param array  $servers       array of all servers available
     * @param array  $clientServers array of servers defined for a client
     * @param string $clientAlias   alias of the client
     *
     * @return array
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function getServers(array $servers, array $clientServers, $clientAlias)
    {
        $serverToAdd = [];
        $toReturn    = [];

        foreach ($clientServers as $clientServer) {
            // wildcard detected
            if ((false !== strpos($clientServer, '*')) or (false !== strpos($clientServer, '?'))) {
                $serverFound = 0;
                foreach ($servers as $serverName => $server) {
                    // serverName match the wildcard
                    if (fnmatch($clientServer, $serverName)) {
                        $serverToAdd[$serverName] = $server;
                        $serverFound++;
                    }
                }
                // no server found
                if (0 === $serverFound) {
                    throw new InvalidConfigurationException("M6WebRedis client $clientAlias used server $clientServer which doesnt match to any servers configured");
                }
            // search a server in server list
            } else {
                if (!array_key_exists($clientServer, $servers)) {
                    throw new InvalidConfigurationException("M6WebRedis client $clientAlias used server $clientServer which is not defined in the servers section");
                }
                $serverToAdd[$clientServer] = $servers[$clientServer];
            }
        }

        foreach ($serverToAdd as $alias => $server) {
            //format the array according to predis client need
            $toReturn[] = $server + array('alias', $alias);
        }

        return $toReturn;
    }


    /**
     * select an alias for the extension
     *
     * trick allowing bypassing the Bundle::getContainerExtension check on getAlias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'm6_redis';
    }
}
