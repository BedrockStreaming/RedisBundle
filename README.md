# RedisBundle [![Build Status](https://travis-ci.org/M6Web/RedisBundle.png?branch=master)](https://travis-ci.org/M6Web/RedisBundle)

symfony2 Bundle on top of m6web/redis-component

see [m6web/redis-component](https://github.com/M6Web/Redis)

## features

* semantic configuration
* sf2 event dispatcher integration
* session handler with redis storage : ```M6Web\Bundle\RedisBundle\Redis\RedisSessionHandler```
* redis adapter for guzzle cache : ```M6Web\Bundle\RedisBundle\Guzzle\RedisCacheAdapter```
* dataCollector for sf2 web profiler toolbar
* cacheResetter option


## usage

### configuration

in ```config.yml``` for a simple cache service :

```
m6_redis:
    servers:
        default:
            ip:   'localhost'
            port: 6379
    clients:
        default:
            servers:   ["default"]     # list of servers to use
            namespace: raoul\          # namespace to use
            timeout:   2               # timeout in second
            readwritetimeout: 2        # read-write timeout in second
```

for a multiple clients :

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
            timeout:   2               # timeout in second (float)
            readwritetimeout: 1.2      # read write timeout in seconds (float)
            reconnect: 1               # number of reconnection attempt if a redis command fail
        sharded:
            servers: ["first", "second"]
            namespace: raaaoul\
            timeout:   1
```

```$this->get('m6redis')``` send the default client. ```this->get('m6redis.sharded')``` the sharded one.

for a direct access to the predis object (without consistant hashing) (```servers``` section remains the same) :

```
m6_redis:
    clients:
        default:
            servers: ["first"]
            type: ["db"]
            timeout: 0.5
        longclient:
            servers: ["first"]
            type: ["db"]
            timeout: 4
```

```$this->get('m6_dbredis')``` send the default client. ```$this->get('m6_dbredis.longclient)``` the other one. Thoses servers can't have more than one server configured.

### server configuration via wildcard

```
m6_redis:
    servers:
        server1:
            ip:   'localhost'
            port: 6379
        server2:
            ip:   'xxxxxxxx'
    clients:
        default:
            servers:   ["server*"]     # all servers matching server*
            namespace: raoul\
            timeout:   2
```


### event dispatcher

The event ```M6Web\Bundle\RedisBundle\EventDispatcher``` is automaticly dispatched to the redis component. Events are fired with the ```redis.command``` label.

### session handler

TODO

### guzzle redis cache adapter

see http://guzzle.readthedocs.org/en/latest/plugins/cache-plugin.html


```
$ttl         = 20; // 20s ttl - override the ttl guessed by cache-control: max-age
$adapter     = new M6\Bundle\RedisBundle\Guzzle\RedisCacheAdapter($this->get('m6redis'), $ttl);
$cachePlugin = new \Guzzle\Plugin\Cache\CachePlugin($adapter);
$guzzleHttpClient->addSubscriber($cachePlugin);
```

### dataCollector

Datacollector is available when the Symfony profiler is enabled. The collector allow you to see the following Redis data:

 - Command name
 - Execution time
 - Command arguments

### overwriting base class

```
m6_redis:
    clients:
        default:
            servers: ["first"]
            type: ["db"]
            timeout: 0.5
            class: \MyCompany\Redis
```

## Launch Unit Tests

```shell
./vendor/bin/atoum
```
