<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebRedisExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $clients = $config['clients'] ?? [];
        foreach ($clients as $clientAlias => $clientConfig) {
            $this->loadClient($container, $clientAlias, $config);
        }

        if ($container->getParameter('kernel.debug')) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('data_collector.yml');
        }
    }

    /**
     * Load a dbclient configuration as a service in the container. A client can use multiple servers
     *
     * @param ContainerInterface $container   The container
     * @param string             $clientAlias Alias of the client
     * @param array              $config      global config
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function loadClient(ContainerInterface $container, string $clientAlias, array $config)
    {
        $options = [];
        $clientConfig = $config['clients'][$clientAlias];

        if (array_key_exists('prefix', $clientConfig)) {
            $options['prefix'] = $clientConfig['prefix'];
        }

        if ($clientConfig['compress'] === true) {
            $options['profile'] = 'compression';
        }

        $options['connections'] = [
            'tcp' => 'M6Web\Bundle\RedisBundle\Connection\StreamConnection',
        ];

        $servers = $this->getServers($config['servers'], $clientConfig['servers'], $clientAlias);
        if (count($servers) === 0) {
            throw new InvalidConfigurationException(sprintf('no server found for client %s', $clientAlias));
        }

        $this->applyClientOptionsToServers($servers, $clientConfig);
        $serviceId = ($clientAlias == 'default') ? 'm6web_redis' : 'm6web_redis.'.$clientAlias;

        $definition = new Definition($clientConfig['class']);
        $definition->setArguments([$servers, $options]);
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
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    protected function getServers(array $servers, array $clientServers, string $clientAlias): array
    {
        $serverToAdd = [];
        $toReturn = [];

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
            $server['alias'] = $alias;
            $toReturn[] = $server;
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
    public function getAlias(): string
    {
        return 'm6web_redis';
    }

    protected function applyClientOptionsToServers(array &$servers, array $clientConfig)
    {
        // force timeout, read write timeout for servers on this client
        $forceOptions = [];

        if (isset($clientConfig['read_write_timeout'])) {
            $forceOptions['read_write_timeout'] = $clientConfig['read_write_timeout'];
        }

        if (isset($clientConfig['timeout'])) {
            $forceOptions['timeout'] = $clientConfig['timeout'];
        }

        if (!empty($forceOptions)) {
            array_walk($servers, function (&$server) use ($forceOptions) {
                $server = array_merge($server, $forceOptions);
            });
        }
    }
}
