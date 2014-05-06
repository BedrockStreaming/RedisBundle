<?php

namespace M6Web\Bundle\RedisBundle\M6WebWsClient;

use M6Web\Bundle\WSClientBundle\Cache\CacheInterface;
use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;

class Redis extends BaseRedis implements CacheInterface
{
    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->redis->set($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }
}
