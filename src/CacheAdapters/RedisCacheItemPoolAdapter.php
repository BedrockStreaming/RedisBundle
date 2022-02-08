<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\CacheAdapters;

use M6Web\Bundle\RedisBundle\Redis\RedisClient;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Class RedisCacheItemPoolAdapter
 */
class RedisCacheItemPoolAdapter extends RedisClient implements CacheItemPoolInterface
{
    /** @var \Closure */
    private $createCacheItem;

    /** @var \Closure */
    private $getItemLifeTime;

    /** @var bool */
    protected $transactionInProgress = false;

    /**
     * @param mixed $parameters connection parameters for one or more servers
     * @param mixed $options    options to configure some behaviours of the client
     */
    public function __construct($parameters = null, $options = null)
    {
        $this->createCacheItem = \Closure::bind(
            function ($key, $value, $isHit, $cacheExpire) {
                $cacheItem = new CacheItem();
                $cacheItem->key = $key;
                $cacheItem->value = $value;
                $cacheItem->isHit = $isHit;
                $cacheItem->defaultLifetime = 0;

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
                if ($item->expiry === null) {
                    return null;
                }

                return (int) (($item->expiry - microtime(true)) * 1000);
            },
            null,
            CacheItem::class
        );

        return parent::__construct($parameters, $options);
    }

    public function getItem($key): CacheItem
    {
        $cacheValue = $this->get($key);
        $cacheExpire = $this->ttl($key);
        $isHit = ($cacheValue !== false) ? 1 : 0;

        $cacheValue = is_string($cacheValue) ? unserialize($cacheValue) : null;

        return ($this->createCacheItem)($key, $cacheValue, $isHit, $cacheExpire);
    }

    public function getItems(array $keys = []): array
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
        return $this->exists($key);
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $this->discard();
        $this->endTransaction();

        // return true, we don't have the result from BaseRedis method
        return true;
    }

    public function deleteItem($key): int
    {
        return $this->del($key);
    }

    public function deleteItems(array $keys): bool
    {
        array_walk($keys, [$this, 'remove']);

        return true;
    }

    /**
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        $ttl = ($this->getItemLifeTime)($item);
        if ($ttl === null) {
            return $this->set($item->getKey(), serialize($item->get()));
        }

        return $this->set($item->getKey(), serialize($item->get()), 'px', $ttl);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->startTransaction();
        $this->save($item);

        // return true, we don't have the result from BaseRedis method
        return true;
    }

    public function commit(): bool
    {
        $this->exec();
        $this->endTransaction();

        // return true, we don't have the result from BaseRedis method
        return true;
    }

    protected function startTransaction()
    {
        if (!$this->transactionInProgress) {
            $this->transactionInProgress = true;
            $this->multi();
        }
    }

    protected function endTransaction()
    {
        $this->transactionInProgress = false;
    }
}
