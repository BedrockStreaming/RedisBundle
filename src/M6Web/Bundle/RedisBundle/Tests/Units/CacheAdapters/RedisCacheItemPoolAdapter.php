<?php
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
                $this->newTestedInstance($this->getRedisMock()),
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
                ->boolean($this->testedInstance->hasItem('myKey'))
                    ->isTrue()
                ->object($item)
                    ->isInstanceof('Psr\Cache\CacheItemInterface')
                ->string($item->get())
                    ->isEqualTo('myValue')
            ->if($this->testedInstance->deleteItem('myKey'))
            ->then
                ->boolean($this->testedInstance->hasItem('myKey'))
                    ->isFalse()
        ;
    }
}
