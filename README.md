# RedisBundle [![Build Status](https://travis-ci.org/M6Web/RedisBundle.png?branch=master)](https://travis-ci.org/M6Web/RedisBundle)

symfony2 Bundle on top of predis

see [predis/predis](https://github.com/nrk/predis)

## features

* semantic configuration
* sf2 event dispatcher integration
* session handler with redis storage : ```M6Web\Bundle\RedisBundle\Redis\RedisSessionHandler```
* redis adapter for guzzle cache : ```M6Web\Bundle\RedisBundle\Guzzle\RedisCacheAdapter```
* dataCollector for sf2 web profiler toolbar


## usage

### configuration

in ```config.yml``` for a simple cache service :

```
m6web_redis:
    servers:
        default:
            host:   'localhost'
            port: 6379
            reconnect: 1
    clients:
        default:
            servers:   ["default"]     # list of servers to use
            prefix:    raoul\          # prefix to use
            timeout:   2               # timeout in second
            read_write_timeout: 2      # read-write timeout in second
```

for a multiple clients :

```
m6web_redis:
    servers:
        first:
            host:   'localhost'
            port: 6379
            reconnect: 1
        second:
            host:   'xxxxxxxx'
    clients:
        default:
            servers:   ["first"]     # list of servers to use
            prefix: raoul\           # prefix to use
            timeout:   2             # timeout in second (float)
            read_write_timeout: 1.2  # read write timeout in seconds (float)
            compress: true           # compress/uncompress data sent/retrieved from redis using gzip, only method SET, SETEX, SETNX and GET are supported
        sharded:
            servers: ["first", "second"]
            prefix: raaaoul\
            timeout:   1
```

```$this->get('m6web_redis')``` send the default client. ```this->get('m6web_redis.sharded')``` the sharded one.

### list of options in servers configuration

 - *host*: IP address or hostname of Redis.
 - *port*: CP port on which Redis is listening to. Default value 6379
 - *database*: Database index (see the SELECT command).
 - *scheme*: Connection scheme, such as 'tcp' or 'unix'. Default value tcp
 - *async_connect*: Performs the connect() operation asynchronously. Default value false
 - *persistent*: Leaves the connection open after a GC collection. Default value false
 - *timeout*:  Timeout for the connect() operation. Default value 10
 - *read_write_timeout*: Timeout for read() and write() operations
 - *reconnect*: Number of reconnection attempt if a redis command fail, only for tcp

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
            prefix: raoul\
            timeout:   2
```


### event dispatcher

The event ```M6Web\Bundle\RedisBundle\EventDispatcher\RedisEvent``` is automaticly dispatched when a command is executed. Events are fired with the ```redis.command``` label.

You can customize the event name through the client configuration : 
 
 ```yml
 m6web_redis:
    clients:
        default:
            eventname: myEventName
```            

### session handler

```
# app/config/config.yml
framework:
  session:
    # ...
    handler_id: session.handler.redis

m6web_redis:
  servers:
    first:
      ip: 'localhost'
      port: 6379
  clients:
    sessions:
      servers: ["first"]
      prefix: sessions\
      timeout: 1

services:
  session.handler.redis:
    class: M6Web\Bundle\RedisBundle\Redis\RedisSessionHandler
    public:    false
    arguments:
      - '@m6web_redis.sessions'
      - 3600
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

#### guzzle redis cache adapter


```yml
m6web_redis:
  clients:
    guzzlehttp:
      servers: ["first"]
      prefix: GuzzleHttp\
      class: M6Web\Bundle\RedisBundle\CacheAdapters\M6WebGuzzleHttp
      timeout: 1
```

## Launch Unit Tests

```shell
bin/atoum
```

## Launch php cs

```shell
    make cs-ci
    make cs-fix
```
