<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Tests\Units;

use M6Web\Bundle\RedisBundle\M6WebRedisBundle as TestedM6WebRedisBundle;

class M6WebRedisBundle extends AbstractTest
{
    protected TestedM6WebRedisBundle $bundle;

    public function testGetContainerExtension(): void
    {
        $this->bundle = new TestedM6WebRedisBundle();
        $this->assert
            ->object($this->bundle->getContainerExtension())
            ->isInstanceOf('M6Web\Bundle\RedisBundle\DependencyInjection\M6WebRedisExtension');
    }
}
