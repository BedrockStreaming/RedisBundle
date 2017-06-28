<?php
namespace M6Web\Bundle\RedisBundle\Tests\Units;

use M6Web\Component\RedisMock\RedisMockFactory;
use mageekguy\atoum;

class AbstractTest extends atoum\test
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
    protected function getRedisMock()
    {
        $factory          = new RedisMockFactory();
        $myRedisMockClass = $factory->getAdapterClass('M6Web\Component\Redis\Cache', true, true);
        $myRedisMock      = new $myRedisMockClass(static::$params, true);

        return $myRedisMock;
    }
}
