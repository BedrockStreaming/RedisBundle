<?php

namespace M6Web\Bundle\RedisBundle\Guzzle;

use Guzzle\Cache;
use M6Web\Bundle\RedisBundle\Redis\Redis;
/**
 * Redis cache adapter
 *
 */
class RedisCacheAdapter extends Cache\AbstractCacheAdapter
{
    private $ttl = null;

    /**
     * RedisCacheAdapter
     *
     * @param \M6Web\Bundle\RedisBundle\Redis\Redis $redis
     * @param null|int                              $ttl
     *
     * @internal param \Guzzle\Cache $cache Redis cache object
     */
    public function __construct(Redis $redis, $ttl = null)
    {
        $this->cache = $redis;

        if ($ttl) {
            $this->ttl = $ttl;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id, array $options = null)
    {
        return $this->cache->has($id);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id, array $options = null)
    {
        return $this->cache->remove($id);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id, array $options = null)
    {
        return unserialize($this->cache->get($id));
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = false, array $options = null)
    {
        if ((is_int($lifeTime) && $lifeTime < 0) or (!$lifeTime)) {
            $lifeTime = $this->ttl;
        }

        return $this->cache->set($id, serialize($data), $lifeTime);
    }
}
