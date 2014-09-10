<?php

namespace M6Web\Bundle\RedisBundle\GuzzleHttp;

use M6Web\Bundle\RedisBundle\Redis\Redis;
use Doctrine\Common\Cache\Cache;

/**
 * Redis cache adapter
 *
 */
class RedisCacheAdapter implements Cache
{
    /**
     * @var interge
     */
    protected $defaultTtl = null;

    /**
     * @var boolean
     */
    protected $forceTtl = false;

    /**
     * @var \M6Web\Bundle\RedisBundle\Redis\Redis
     */
    protected $cache = null;

    /**
     * RedisCacheAdapter
     *
     * @param \M6Web\Bundle\RedisBundle\Redis\Redis $redis
     * @param null|int                              $defaultTtl
     * @param boolean                               $forceTtl
     *
     * @internal param \Guzzle\Cache $cache Redis cache object
     */
    public function __construct(Redis $redis, $defaultTtl = null, $forceTtl = false)
    {
        $this->cache = $redis;

        if ($defaultTtl) {
            $this->defaultTtl = $defaultTtl;
        }

        if ($forceTtl) {
            $this->forceTtl = $forceTtl;
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
        if ($this->forceTtl or (is_int($lifeTime) && $lifeTime < 0) or (!$lifeTime)) {
            $lifeTime = $this->defaultTtl;
        }

        return $this->cache->set($id, serialize($data), $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        $info = $this->cache->info();
        return array(
            Cache::STATS_HITS             => false,
            Cache::STATS_MISSES           => false,
            Cache::STATS_UPTIME           => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE     => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE => false
        );
    }
}
