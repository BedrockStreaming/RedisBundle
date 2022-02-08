<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Connection;

use Predis\Connection\ConnectionException;
use Predis\Connection\StreamConnection as PredisStreamConnection;

class StreamConnection extends PredisStreamConnection
{
    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        $errors = 0;
        $maxConnectionLostAllowed = $this->parameters->reconnect;

        do {
            try {
                return parent::connect();
            } catch (ConnectionException $exception) {
                $errors++;
            }
        } while ($maxConnectionLostAllowed >= $errors);

        throw $exception;
    }
}
