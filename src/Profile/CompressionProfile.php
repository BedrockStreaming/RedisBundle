<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Profile;

use B1rdex\PredisCompressible\Compressor\GzipCompressor;
use B1rdex\PredisCompressible\CompressProcessor;
use Predis\Profile\RedisProfile;

class CompressionProfile extends RedisProfile
{
    /**
     * Strings with length > 2048 bytes will be compressed
     */
    const DEFAULT_THRESHOLD = 1024;

    /**
     * {@inheritdoc}
     */
    public function createCommand($commandID, array $arguments = [])
    {
        $command = parent::createCommand($commandID, $arguments);

        $compressor = new GzipCompressor(self::DEFAULT_THRESHOLD);
        $compressProcessor = new CompressProcessor($compressor);
        $compressProcessor->process($command);

        return $command;
    }

    protected function getSupportedCommands()
    {
        return [
            'SET' => 'B1rdex\PredisCompressible\Command\StringSet',
            'SETEX' => 'B1rdex\PredisCompressible\Command\StringSetExpire',
            'SETNX' => 'B1rdex\PredisCompressible\Command\StringSetPreserve',
            'GET' => 'B1rdex\PredisCompressible\Command\StringGet',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '3.2';
    }
}
