<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Redis;

use Predis\ClientInterface;

/**
 * session handler
 */
class RedisSessionHandler implements \SessionHandlerInterface
{
    protected $redis;
    protected $maxLifetime;

    public function __construct(ClientInterface $redis, int $maxLifetime)
    {
        $this->maxLifetime = $maxLifetime;
        $this->redis = clone $redis;
    }

    /**
     * getter for the redis object
     */
    public function getRedis(): ClientInterface
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId): string|false
    {
        try {
            if (!$this->redis->exists($sessionId)) {
                // session does not exist, create it empty
                $this->redis->set($sessionId, '', $this->maxLifetime);

                return false;
            }
            // each read increment lifetime
            $this->redis->expire($sessionId, $this->maxLifetime);

            return $this->redis->get($sessionId);
        } catch (\M6Web\Component\Redis\Exception $e) {
            throw new \RuntimeException('Error reading session : '.$e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        try {
            $this->redis->set($sessionId, (string) $data, $this->maxLifetime);

            return true;
        } catch (\M6Web\Component\Redis\Exception $e) {
            throw new \RuntimeException('Error writing session : '.$e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        try {
            return $this->redis->del($sessionId) > 0;
        } catch (\M6Web\Component\Redis\Exception $e) {
            throw new \RuntimeException('Error destroying session : '.$e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime): int|false
    {
        return 1;
    }
}
