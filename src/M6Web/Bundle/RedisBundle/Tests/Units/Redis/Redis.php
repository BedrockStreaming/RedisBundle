<?php
namespace M6Web\Bundle\RedisBundle\Redis\tests\units;

use mageekguy\atoum;
use M6Web\Component\Redis\Cache;
use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;
use M6Web\Component\RedisMock\RedisMockFactory;

/**
* Test compute last modified date
*
*/
class Redis extends atoum\test
{
    static protected $params = array(
        'namespace' => '__tt____',
        'timeout' => 2,
        'compress' => true,
        'server_config' => array(
            'local' => array(
                'ip' => 'localhost',
                'port' => 6379,
            )
        )
    );

    /**
     * get a redis Instance
     *
     * @return BaseRedis
     */
    protected function getRedisInstance()
    {
        $factory     = new RedisMockFactory();
        $myRedisMockClass = $factory->getAdapterClass('Predis\Client', true, true);
        $myRedisMock = new $myRedisMockClass(static::$params, true);
        $redis = new BaseRedis($myRedisMock);

        return $redis;
    }

    /**
     * Test call on injected object
     *
     * @dataProvider decoratorCallsDataProvider
     *
     * @return void
     */
    public function testDecoratorCalls($srcMethod, $srcArgs, $targetMethod, $targetArgs)
    {
        $redis = $this->getRedisInstance();

        $controller                = new \atoum\mock\controller();
        $controller->__construct   = function() {}; // overwrite constructor
        $controller->$targetMethod = function() {}; // overwrite del method
        $redisObject               = new \mock\Predis\Client;
        $redis->setRedis($redisObject);

        $this->if(call_user_func_array([$redis, $srcMethod], $srcArgs))
            ->then
            ->mock($redisObject)
                ->call($targetMethod)
                     ->withAtLeastArguments($targetArgs)
                        ->once();
    }

    public function decoratorCallsDataProvider()
    {
        return [
            ['get',    ['foo'],        'get',    ['foo']],
            ['has',    ['foo'],        'exists', ['foo']],
            ['remove', ['foo'],        'del',    ['foo']],
            ['set',    ['foo', 'bar'], 'set',    ['foo', 'bar']],
            ['ttl',    ['foo'],        'ttl',    ['foo']],
            ['del',    ['raoul'],      'del',    ['raoul']]
        ];
    }
}
