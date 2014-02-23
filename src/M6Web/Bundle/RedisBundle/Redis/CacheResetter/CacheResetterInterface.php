<?php

namespace M6Web\Bundle\RedisBundle\Redis\CacheResetter;

/**
 * Interface CacheResetterInterface
 *
 * @package M6Web\Bundle\RedisBundle\Redis\CacheResetter
 *
 */
interface CacheResetterInterface
{
    /**
     * Checks if the cache must be reset or not
     *
     * @return Boolean true if the cache must be clear or false otherwise
     */
    public function shouldResetCache();
}
