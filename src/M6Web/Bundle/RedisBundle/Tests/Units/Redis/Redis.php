<?php
namespace M6Web\Bundle\RedisBundle\Redis\tests\units;

require_once __DIR__.'/../../../../../../../vendor/autoload.php';

use M6Web\Bundle\RedisBundle\Tests\Units\AbstractTest;
use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;

/**
* Test compute last modified date
*
*/
class Redis extends AbstractTest
{
    /**
     * get a redis Instance
     *
     * @return BaseRedis
     */
    protected function getRedisInstance()
    {
        return new BaseRedis($this->getRedisMock());
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

        $controller = new \atoum\mock\controller();
        $controller->__construct = function() {}; // overwrite constructor
        $controller->$targetMethod     = function() {}; // overwrite given method

        $redisObject = new \mock\M6Web\Component\Redis\Cache($controller);
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
