<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Tests\Units\Redis;

use M6Web\Bundle\RedisBundle\Tests\Units\AbstractTest;
use M6Web\Bundle\RedisBundle\Tests\Units\Mock\StreamConnectionMock;

/**
 * Test compute last modified date
 */
class RedisClient extends AbstractTest
{
    /**
     * Test call on injected object
     *
     * @dataProvider decoratorCallsDataProvider
     */
    public function testDecoratorCalls($srcMethod, $srcArgs)
    {
        $redisClient = $this->newTestedInstance('tcp://127.0.0.1', [
            'connections' => ['tcp' => StreamConnectionMock::class],
        ]);
        $redisClient->setEventDispatcher($eventDispatcher = $this->getEventDispatcherMock());
        $this
            ->if(call_user_func_array([$redisClient, $srcMethod], $srcArgs))
            ->mock($eventDispatcher)
                ->call('dispatch')
                    ->once()
        ;
    }

    public function decoratorCallsDataProvider()
    {
        return [
            ['get',    ['foo']],
            ['exists',    ['foo']],
            ['del',    ['foo']],
            ['set',    ['foo', 'bar']],
            ['ttl',    ['foo']],
            ['del',    ['raoul']],
            ['mget',   [[['foo', 'bar']]]],
            ['mset',   [['foo' => 'fighters'], ['bar' => 'baz']]],
        ];
    }

    protected function getEventDispatcherMock()
    {
        $mock = new \Mock\Symfony\Component\EventDispatcher\EventDispatcher();
        $mock->getMockController()->dispatch = function(): object { return new \StdClass; };

        return $mock;
    }
}
