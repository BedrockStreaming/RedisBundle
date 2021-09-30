<?php

declare(strict_types=1);

namespace M6Web\Bundle\RedisBundle\Tests\Units\CacheAdapters;

use M6Web\Bundle\RedisBundle\Tests\Units\AbstractTest;

/**
 * Class RedisCacheItemPoolAdapter
 */
class RedisCacheItemPoolAdapter extends AbstractTest
{
    public function testCreateItem()
    {
        $this
            ->given(
                $this->newTestedInstance('tcp://127.0.0.1', [
                    'connections' => ['tcp' => '\M6Web\Bundle\RedisBundle\Tests\Units\Mock\StreamConnectionMock'],
                ]),
                $item = $this->testedInstance->getItem('myKey')
            )
            ->then
                ->object($item)
                    ->isInstanceof('Psr\Cache\CacheItemInterface')
                ->variable($item->get())
                    ->isNull()
            ->given(
                $item->set('myValue'),
                $item->expiresAt(new \DateTime('+1 second')),
                $this->testedInstance->save($item)
            )
            ->then
                ->integer($this->testedInstance->hasItem('myKey'))
                    ->isEqualTo(1)
                ->object($item)
                    ->isInstanceof('Psr\Cache\CacheItemInterface')
                ->string($item->get())
                    ->isEqualTo('myValue')
            ->if($this->testedInstance->deleteItem('myKey'))
            ->then
                ->integer($this->testedInstance->hasItem('myKey'))
                    ->isEqualTo(0)
        ;
    }


    public function testExpirableItem()
    {
        $this
            ->given(
                $this->newTestedInstance('tcp://127.0.0.1', [
                    'connections' => ['tcp' => '\M6Web\Bundle\RedisBundle\Tests\Units\Mock\StreamConnectionMock'],
                ]),
                $item = $this->testedInstance->getItem('myKey'),
                $item->set('myExpirableValue'),
                $item->expiresAt(new \DateTime('+10 milliseconds')),
                $this->testedInstance->save($item)
            )
            ->then
                ->integer($this->testedInstance->hasItem('myKey'))
                    ->isEqualTo(1)
                ->object($item)
                    ->isInstanceof('Psr\Cache\CacheItemInterface')
                ->string($item->get())
                    ->isEqualTo('myExpirableValue')
            ->if(sleep(1))
            ->then
                ->integer($this->testedInstance->hasItem('myKey'))
                    ->isEqualTo(0)
        ;
    }
}
