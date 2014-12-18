<?php
namespace M6Web\Bundle\RedisBundle\Redis;

use Predis\Client;
use Symfony\Component\EventDispatcher;
use M6Web\Bundle\RedisBundle\EventDispatcher\RedisEvent;

/**
 * Class Redis
 * client class over the Cache component
 *
 * @package M6Web\Bundle\RedisBundle\Redis
 */
class Redis
{
    /**
     * @var Predis\Client
     */
    protected $redis = null;

    /**
     * event dispatcher
     * @var EventDispatcher
     */
    protected $eventDispatcher = null;

    /**
     * class of the event notifier
     * @var string
     */
    protected $eventClass = null;

    /**
     * eventName to be dispatched
     * @var array
     */
    protected $eventName = 'redis.command';


    /**
     * constructor
     *
     * @param Client $redis
     */
    public function __construct(Client $redis)
    {

        return $this->setRedis($redis);
    }

    /**
     * set the predis object
     *
     * @param Client $redis
     *
     * @return $this
     */
    public function setRedis(Client $redis)
    {
        $this->redis = $redis;

        return $this;
    }

    /**
     * Check if a key exists in redis
     * An alias for exists
     *
     * @param string $key The key we want to check existenz
     *
     * @return Boolean return true if key exists
     */
    public function has($key)
    {
        return $this->exists($key);
    }

    /**
     * Remove a key from redis
     * An alias for del
     *
     * @param string $key The key we want to remove
     *
     * @return integer 1 if deleted
     */
    public function remove($key)
    {
        return $this->del($key);
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
        if (null !== $ttl)
        {

            return $this->redis->setex($key, $ttl, $value);
        } else {

            return $this->redis->set($key, $value);
        }
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
     * @param string $command   redis command
     * @param mixed  $arguments arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($command, $arguments)
    {
        $start = microtime(true);
        $ret   = call_user_func_array(array($this->redis, $command), $arguments);
        $this->notifyEvents($command, $arguments, microtime(true) - $start);

        return $ret;
    }

    /**
     * @param EventDispatcher\EventDispatcherInterface $eventDispacher sf2 Event dispatcher
     *
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher\EventDispatcherInterface $eventDispacher)
    {
        $this->eventDispatcher = $eventDispacher;

        return $this;
    }

    /**
     * @param string $name event name to fire
     *
     * @return $this
     */
    public function setEventName($name)
    {
        $this->eventName = $name;

        return $this;
    }

    /**
     * Notify an event to the event dispatcher
     * @param string $command   The command name
     * @param array  $arguments args of the command
     * @param int    $time      exec time
     *
     * @return \M6Web\Component\Redis\Manager
     */
    public function notifyEvents($command, $arguments, $time)
    {
        if ($this->eventDispatcher) {
            $event = new RedisEvent();
            $event->setCommand($command);
            $event->setExecutionTime($time);
            $event->setArguments($arguments);
            $this->eventDispatcher->dispatch($this->eventName, $event);
        }

        return $this;
    }



}
