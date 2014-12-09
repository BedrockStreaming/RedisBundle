<?php

namespace M6Web\Bundle\RedisBundle\M6WebCacheExtra;

use M6Web\Component\CacheExtra\CacheInterface;
use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;

/**
 * Redis client implementing CacheInterface of CacheExtra Bundle
 *
 * @package M6Web\Bundle\RedisBundle\M6WebCacheExtra
 */
class Redis extends BaseRedis implements CacheInterface
{
}