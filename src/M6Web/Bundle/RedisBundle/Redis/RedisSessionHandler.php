<?php
namespace M6Web\Bundle\RedisBundle\Redis;

/**
 * handler de session sf2 utilisant Redis
 */
class RedisSessionHandler implements \SessionHandlerInterface
{

    protected $redis;
    protected $maxLifetime;

    /**
     * contructor
     * @param Redis   $redis       instance of Redis
     * @param integer $maxLifetime max lifetime of Redis storage
     */
    public function __construct(Redis $redis, $maxLifetime)
    {
        $this->maxLifetime = $maxLifetime;
        $this->redis = clone $redis;
        $this->redis->setNamespace($redis->getNamespace().'_'.str_replace('\\', '_', __CLASS__).'__');
    }

    /**
     * getter for the redis object
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        try {
            if (!$this->redis->exists($sessionId)) {
                // session does not exist, create it empty
                $this->redis->set($sessionId, '', $this->maxLifetime);

                return null;
            } else {
                // each read increment lifetime
                $this->redis->expire($sessionId, $this->maxLifetime);

                return $this->redis->get($sessionId);
            }
        } catch (\M6Web\Component\Redis\Exception $e) {
            throw new \RuntimeException("Error reading session : ".$e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        try {
            $this->redis->set($sessionId, (string) $data, $this->maxLifetime);

            return true;
        } catch (\M6Web\Component\Redis\Exception $e) {
            throw new \RuntimeException("Error writing session : ".$e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        try {
            $ret = $this->redis->del($sessionId);
            if ($ret > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\M6Web\Component\Redis\Exception $e) {
            throw new \RuntimeException("Error destroying session : ".$e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

}
