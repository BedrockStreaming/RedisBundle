<?php
namespace M6Web\Bundle\RedisBundle\CacheAdapters;

use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;
use M6Web\Bundle\GuzzleHttpBundle\Cache\CacheInterface;

/**
 * Redis cache adapter for M6WebGuzzleHttp client
 */
class M6WebGuzzleHttp extends BaseRedis implements CacheInterface
{
}