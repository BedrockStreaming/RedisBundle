<?php

namespace M6Web\Bundle\RedisBundle\M6WebWsClient;

use M6Web\Bundle\WSClientBundle\Cache\CacheInterface;
use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;

/**
 * Redis adapter for M6WebWsClient
 */
class Redis extends BaseRedis implements CacheInterface
{
}
