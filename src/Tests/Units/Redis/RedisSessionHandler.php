<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Tests\Units\Redis;

use mageekguy\atoum;
use M6Web\Component\RedisMock\RedisMockFactory;
use M6Web\Bundle\RedisBundle\Redis\RedisSessionHandler as BaseRedisSessionHandler;
use M6Web\Bundle\RedisBundle\Redis\RedisClient as BaseRedis;

/**
 * Class RedisSessionHandler
 */
class RedisSessionHandler extends atoum
{
    /**
     * get a Redis instance
     *
     * @param int $testId
     *
     * @return BaseRedis
     */
    protected function getRedisInstance($testId)
    {
        $params = [
            'namespace' => '__tt__'.$testId.'__',
            'timeout' => 2,
            'compress' => true,
            'server_config' => [
                'local' => [
                    'ip' => 'localhost',
                    'port' => 6379,
                    ],
                ],
            ];
        $factory = new RedisMockFactory();
        $myRedisMockClass = $factory->getAdapterClass('M6Web\Bundle\RedisBundle\Redis\RedisClient', true, true);
        $myRedisMock = new $myRedisMockClass($params, true);

        return $myRedisMock;
    }

    /**
     * Test the constructor
     */
    public function testConstructor()
    {
        $s = new BaseRedisSessionHandler($this->getRedisInstance(1), 10);
        $this->if($redis = $s->getRedis())
        ->class('M6Web\Bundle\RedisBundle\Redis\RedisClient')
        ;
    }

    /**
     * test open gc and close
     */
    public function testDummyMethods()
    {
        $s = new BaseRedisSessionHandler($this->getRedisInstance(2), 10);
        $this->assert
        ->boolean($s->open('test', 'test'))
        ->isIdenticalTo(true)
        ->boolean($s->gc(10))
        ->isIdenticalTo(true)
        ->boolean($s->close())
        ->isIdenticalTo(true);
    }

    /**
     * test read, write
     */
    public function testReadWrite()
    {
        $s = new BaseRedisSessionHandler($this->getRedisInstance(3), 10);
        $this->assert
        // ->enableDebugMode()
        ->boolean($e = $s->write('test', 'data'))
        // ->dump($e)
        ->isIdenticalTo(true)
        ->string($s->read('test'))
        ->isIdenticalTo('data');

        $this->assert
        ->variable($s->read('test2'))->isIdenticalTo('')
        ->boolean($s->write('test2', 'toto'))
        ->isIdenticalTo(true)
        ->boolean($s->destroy('test2'))
        ->isIdenticalTo(true)
        ->variable($s->read('test2'))->isNull();

        $this->assert
        ->boolean($s->destroy('raoul'))
        ->isIdenticalTo(false);
    }
}
