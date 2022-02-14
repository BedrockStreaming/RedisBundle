<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Profile;

use B1rdex\PredisCompressible\Command\StringGet;
use B1rdex\PredisCompressible\Command\StringGetMultiple;
use B1rdex\PredisCompressible\Command\StringSet;
use B1rdex\PredisCompressible\Command\StringSetExpire;
use B1rdex\PredisCompressible\Command\StringSetMultiple;
use B1rdex\PredisCompressible\Command\StringSetPreserve;
use B1rdex\PredisCompressible\Compressor\GzipCompressor;
use B1rdex\PredisCompressible\CompressProcessor;
use Predis\Profile\RedisProfile;

class CompressionProfile extends RedisProfile
{
    /**
     * {@inheritdoc}
     */
    public function createCommand($commandID, array $arguments = [])
    {
        $command = parent::createCommand($commandID, $arguments);

        $compressor = new GzipCompressor();
        $compressProcessor = new CompressProcessor($compressor);
        $compressProcessor->process($command);

        return $command;
    }

    protected function getSupportedCommands()
    {
        return [
            'SET' => StringSet::class,
            'SETEX' => StringSetExpire::class,
            'SETNX' => StringSetPreserve::class,
            'GET' => StringGet::class,
            'MSET' => StringSetMultiple::class,
            'MGET' => StringGetMultiple::class,
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
