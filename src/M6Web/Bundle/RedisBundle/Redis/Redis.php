<?php
namespace M6Web\Bundle\RedisBundle\Redis;

use M6Web\Component\Redis\Cache;

/**
 * Class Redis
 * client class over the Cache component
 *
 * @package M6Web\Bundle\RedisBundle\Redis
 */
class Redis
{
    /**
     * @var Cache
     */
    protected $redis = null;

    /**
     * constructeur
     *
     * @param Cache $redis
     */
    public function __construct(Cache $redis)
    {
        $this->setRedis($redis);
    }

    /**
     * set the Redis
     *
     * @param Cache $redis
     *
     * @return $this
     */
    public function setRedis(Cache $redis)
    {
        $this->redis = $redis;

        return $this;
    }

    /**
     * get Redis
     *
     * @return Cache
     */
    public function getRedis()
    {
        return $this->redis;
    }


    /**
     * Get a redis key. If the refresh cache option is set, return false
     *
     * @param string $key The get we want
     *
     * @return string Result
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * Check if a key exists in redis
     *
     * @param string $key The key we want to check existenz
     *
     * @return Boolean return true if key exists
     */
    public function has($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * Remove a key from redis
     *
     * @param string $key The key we want to remove
     *
     * @return integer 1 if deleted
     */
    public function remove($key)
    {
        return $this->redis->del($key);
    }

    /**
     * Define value for given key
     *
     * @param string  $key
     * @param string  $value
     * @param integer $ttl
     *
     * @return Boolean
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->redis->set($key, $value, $ttl);
    }

    /**
     * Return TTL for given key
     *
     * @param string $key
     *
     * @return integer
     */
    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }

    /**
     * pass unkown methods to the redis object
     *
     * @param mixed $name
     * @param mixed $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if ($this->redis) {

            return call_user_func_array(array($this->redis, $name), $arguments);
        } else {
            throw new Exception('Redis object is null !');
        }
    }

}
