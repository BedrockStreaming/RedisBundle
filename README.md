# RedisBundle [![Build Status](https://travis-ci.org/M6Web/RedisBundle.png?branch=master)](https://travis-ci.org/M6Web/RedisBundle)

symfony2 Bundle on top of m6web/redis-component

see [m6web/redis-component](https://github.com/M6Web/Redis)

## features

* semantic configuration
* sf2 event dispatcher integration
* session handler with redis storage : ```M6Web\Bundle\RedisBundle\Redis\RedisSessionHandler```
* redis adapter for guzzle cache : ``M6Web\Bundle\RedisBundle\Guzzle\RedisCacheAdapter```
* dataCollector for sf2 web profiler toolbar
* cacheResetter option


## usage

### configuration

in ```config.yml``` for a simple cache service

```
m6_redis:
    cache_resetter:     'm6.cache_resetter' # allow you to set a service in order to know if the cache should be reseted or not - implement M6Web\Bundle\RedisBundle\Redis\CacheResetter\CacheResetterInterface
    servers:
        default:
            ip:   'localhost'
            port: 6379
    clients:
        default:
            servers:   ["default"]     # list of servers to use
            namespace: raoul\          # namespace to use
            timeout:   2               # timeout in second
```

for a multiple clients

```
m6_redis:
    servers:
        first:
            ip:   'localhost'
            port: 6379
        second:
            ip:   'xxxxxxxx'
    clients:
        default:
            servers:   ["first"]     # list of servers to use
            namespace: raoul\          # namespace to use
            timeout:   2               # timeout in second
        sharded:
            servers: ["first", "second"]
            namespace: raaaoul\
            timeout:   1
```

```$this->get('m6redis')``` send the default client. ```this->get('m6redis.sharded')``` the sharded one.

### event dispatcher

TODO

### session handler

TODO

### guzzle redis cache adapter

TODO

### dataCollector

TODO

### cacheResetter

TODO

## Launch Unit Tests

```shell
./vendor/bin/atoum
```
