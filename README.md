# RedisBundle [![Build Status](https://travis-ci.org/M6Web/RedisBundle.png?branch=master)](https://travis-ci.org/M6Web/RedisBundle)

symfony2 Bundle on top of predis

see [predis/predis]()

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
m6web_redis:
    servers:
        default:
            host:   'localhost'
            port: 6379
    clients:
        default:
            servers:   ["default"]     # list of servers to use
            namespace: raoul\          # namespace to use
            timeout:   2               # timeout in second
```

for a multiple clients :

```
m6web_redis:
    servers:
        first:
            host:   'localhost'
            port: 6379
        second:
            host:   'xxxxxxxx'
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

```$this->get('m6web_redis')``` send the default client. ```this->get('m6web_redis.sharded')``` the sharded one.

### sf2 events

### list of options in servers configuration

TODO 

```
m6web_redis:
    servers:
        server1:
            host:   'localhost'
            port: 6379

```

### server configuration via wildcard

```
m6web_redis:
    servers:
        server1:
            host:   'localhost'
            port: 6379
        server2:
            host:   'xxxxxxxx'
    clients:
        default:
            servers:   ["server*"]     # all servers matching server*
            namespace: raoul\
            timeout:   2
```


### event dispatcher

The event ```M6Web\Bundle\RedisBundle\EventDispatcher``` is automaticly dispatched to the redis component. Events are fired with the ```redis.command``` label.

TODO : explain how to change by client 

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
m6web_redis:
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
