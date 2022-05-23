<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * M6RedisBundle
 */
class M6WebRedisBundle extends Bundle
{
    /**
     * trick allowing bypassing the Bundle::getContainerExtension check on getAlias
     * not very clean, to investigate
     *
     * @return object DependencyInjection\M6WebStatsdExtension
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DependencyInjection\M6WebRedisExtension();
    }
}
