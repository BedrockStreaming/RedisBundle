<?php
namespace M6Web\Bundle\RedisBundle\DependencyInjection\tests\units;

use mageekguy\atoum;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use M6Web\Bundle\RedisBundle\DependencyInjection\M6WebRedisExtension as BaseM6WebRedisExtension;
use Symfony\Component\EventDispatcher\EventDispatcher;


class M6WebRedisExtension extends atoum\test
{

    /**
     * @var BaseM6WebRedisExtension
     */
    protected  $extension;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     *
     */
    protected function initContainer()
    {
        $this->extension = new BaseM6WebRedisExtension();

        $this->container = new ContainerBuilder();
        $this->container->register('event_dispatcher', new EventDispatcher());
        $this->container->registerExtension($this->extension);
    }

    /**
     * @param ContainerBuilder $container
     * @param                  $resource
     */
    protected function loadConfiguration(ContainerBuilder $container, $resource)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../Fixtures/'));
        $loader->load($resource.'.yml');
    }

    public function testBasicConfiguration()
    {
        $this->initContainer();
        $this->loadConfiguration($this->container, 'basic_config');
        $this->container->compile();

        $this->assert
                ->boolean($this->container->has('m6_redis'))
                    ->isIdenticalTo(true)
            ->and()
                ->object($serviceRedis = $this->container->get('m6_redis'))
                    ->isInstanceOf('M6Web\Bundle\RedisBundle\Redis\Redis')
            ->and()
                ->integer($serviceRedis->getConnection()->count())
                    ->isEqualTo(1)
                ->string((string) $serviceRedis->getConnection()->getIterator()['server1'])
                    ->isEqualTo('lolcathost:6379')
                ->string($serviceRedis->getOptions()->prefix->getPrefix())
                    ->isEqualTo('raoul\\')
                ->integer($serviceRedis->getConnection()->getIterator()['server1']->getParameters()->timeout)
                    ->isEqualTo(2)
                ->string($serviceRedis->getConnection()->getIterator()['server1']->getParameters()->alias)
                    ->isEqualTo('server1');
    }

    public function test2serverConfiguration()
    {
        $this->initContainer();
        $this->loadConfiguration($this->container, '2servers_config');
        $this->container->compile();

        $this->assert
                ->boolean($this->container->has('m6_redis'))
                ->isIdenticalTo(true)
            ->and()
                ->object($serviceRedis = $this->container->get('m6_redis')) // default client
                    ->isInstanceOf('M6Web\Bundle\RedisBundle\Redis\Redis')
            ->and()
                ->integer($serviceRedis->getConnection()->count())
                ->isEqualTo(2)

                ->string((string) $serviceRedis->getConnection()->getIterator()['server1'])
                    ->isEqualTo('localhost:6379')
                ->integer($serviceRedis->getConnection()->getIterator()['server1']->getParameters()->timeout)
                    ->isEqualTo(2)
                ->string($serviceRedis->getConnection()->getIterator()['server1']->getParameters()->alias)
                    ->isEqualTo('server1')
            ->and()
                ->string((string) $serviceRedis->getConnection()->getIterator()['server2'])
                    ->isEqualTo('raoulhost:6379')
                ->integer($serviceRedis->getConnection()->getIterator()['server2']->getParameters()->timeout)
                    ->isEqualTo(4)
                ->string($serviceRedis->getConnection()->getIterator()['server2']->getParameters()->alias)
                    ->isEqualTo('server2');

         // test clients
        $this->if($serviceRedis = $this->container->get('m6_redis.all'))
            ->then()
                ->integer($serviceRedis->getConnection()->count())
                    ->isEqualTo(2)
                ->string($serviceRedis->getOptions()->prefix->getPrefix())
                    ->isEqualTo('tousmaistous\\');

        $this->if($serviceRedis = $this->container->get('m6_redis.one'))
            ->then()
                ->integer($serviceRedis->getConnection()->count())
                    ->isEqualTo(1)
                ->string($serviceRedis->getOptions()->prefix->getPrefix())
                    ->isEqualTo('server1\\');
    }
} 