<?php
namespace M6Web\Bundle\RedisBundle\DependencyInjection\tests\units;

use mageekguy\atoum;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use M6Web\Bundle\RedisBundle\DependencyInjection\M6WebRedisExtension as BaseM6WebRedisExtension;
use Symfony\Component\EventDispatcher\EventDispatcher;
use M6Web\Bundle\RedisBundle\EventDispatcher\RedisEvent;


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
        $this->container->setParameter('kernel.debug', true);
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

    // test not working
//    public function testDataCollector()
//    {
//        $this->initContainer();
//        $this->loadConfiguration($this->container, 'basic_config');
//        $this->container->compile();
//
//        $this->assert
//                ->object($dataCollector = $this->container->get('m6.data_collector.redis'))
//                    ->isInstanceOf('M6Web\Bundle\RedisBundle\DataCollector\RedisDataCollector');
//
//        $event = new RedisEvent();
//        $event->setCommand('one_command');
//        $event->setExecutionTime(0.02);
//        $event->setArguments(['foo', 'bar']);
//        $event->setClientName('redis.command');
//
//        $this->if($eventDispatcher = $this->container->get('event_dispatcher'))
//            ->then($eventDispatcher->dispatch('redis.command', $event))
//            ->object($this->container->get('m6.data_collector.redis')->getCommands())
//                ->isInstanceOf('\SplQueue')
//            ->integer(count($dataCollector->getCommands()))
//                ->isEqualto(1)
//        ;
//        var_dump($this->container->get('m6.data_collector.redis')->getCommands());
//        // TODO
//
//
//    }

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


        // this demonstrate that the consistent hashing isnt fully functionnal
        // https://github.com/nrk/predis/issues/225
//        $faker = Faker\Factory::create();
        // test sharding
//        for ($i=0;$i<=1000;$i++) {
//            $this->container->get('m6_redis.all')->set('coucou', 'coucou', 20);
//        }
    }
} 