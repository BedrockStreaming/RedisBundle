<?php
namespace M6Web\Bundle\RedisBundle\CacheAdapters;

use M6Web\Bundle\RedisBundle\Redis\Redis as BaseRedis;

use Symfony\Component\Cache\CacheItem;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

/**
 * Class RedisCacheItemPoolAdapter
 */
class RedisCacheItemPoolAdapter extends BaseRedis implements CacheItemPoolInterface
{
    /**
     * @var \Closure
     */
    private $createCacheItem;

    /**
     * @var \Closure
     */
    private $getItemLifeTime;

    /**
     * @var bool
     */
    protected $transactionInProgress = false;

    public function __construct($redis, $defaultLifetime = 0)
    {
        $this->createCacheItem = \Closure::bind(
            function ($key, $value, $isHit, $cacheExpire) use ($defaultLifetime) {
                $cacheItem = new CacheItem();
                $cacheItem->key = $key;
                $cacheItem->value = $value;
                $cacheItem->isHit = $isHit;
                $cacheItem->defaultLifetime = $defaultLifetime;

                if (is_int($cacheExpire) && $cacheExpire > time()) {
                    $cacheItem->expiry = $cacheExpire;
                }

                return $cacheItem;
            },
            null,
            CacheItem::class
        );

        $this->getItemLifeTime = \Closure::bind(
            function (CacheItem $item) {

                return $item->expiry;
            },
            null,
            CacheItem::class
        );

        return parent::__construct($redis);
    }

    /**
     * @param string $key
     *
     * @return CacheItem
     */
    public function getItem($key)
    {
        $cacheValue = $this->get($key);
        $cacheExpire = $this->ttl($key);
        $isHit = ($cacheValue !== false) ? 1 : 0;

        $cacheValue = is_string($cacheValue) ? unserialize($cacheValue) : null;

        return ($this->createCacheItem)($key, $cacheValue, $isHit, $cacheExpire);
    }

    /**
     * @param array $keys
     *
     * @return array
     */
    public function getItems(array $keys = array())
    {
        $items = new \ArrayIterator();
        foreach ($keys as $key) {
            $items->append($this->getItem($key));
        }

        return $items;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasItem($key)
    {
        return $this->has($key);
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $this->redis->discard();
        $this->endTransaction();

        // return true, we don't have the result from BaseRedis method
        return true;
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function deleteItem($key)
    {
        return $this->remove($key);
    }

    /**
     * @param array $keys
     *
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        array_walk($keys, [$this, 'remove']);

        return true;
    }

    /**
     * @param CacheItemInterface $item
     *
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        return $this->set($item->getKey(), serialize($item->get()), ($this->getItemLifeTime)($item));
    }

    /**
     * @param CacheItemInterface $item
     *
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->startTransaction();
        $this->save($item);

        // return true, we don't have the result from BaseRedis method
        return true;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        $this->redis->exec();
        $this->endTransaction();

        // return true, we don't have the result from BaseRedis method
        return true;
    }

    protected function startTransaction()
    {
        if (!$this->transactionInProgress) {
            $this->transactionInProgress = true;
            $this->redis->multi();
        }
    }

    protected function endTransaction()
    {
        $this->transactionInProgress = false;
    }
}
