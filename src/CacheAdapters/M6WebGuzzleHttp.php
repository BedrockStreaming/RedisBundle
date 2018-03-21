<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\CacheAdapters;

use M6Web\Bundle\RedisBundle\Redis\RedisClient;
use M6Web\Bundle\GuzzleHttpBundle\Cache\CacheInterface;

/**
 * Redis cache adapter for M6WebGuzzleHttp client
 */
class M6WebGuzzleHttp extends RedisClient implements CacheInterface
{
    public function set($key, $value, $ttl = null)
    {
        if (null !== $ttl) {
            return $this->setex($key, $ttl, $value);
        }

        return parent::set($key, $value);
    }

    public function get($key)
    {
        return parent::get($key);
    }

    public function ttl($key): int
    {
        return parent::ttl($key);
    }

    public function has($key)
    {
        return parent::exists($key);
    }

    public function remove($key)
    {
        return parent::del($key);
    }
}
