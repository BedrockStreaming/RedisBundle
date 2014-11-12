# Upgrading

This document will be updated to list important BC breaks and behavorial changes.

## Upgrading to 2.0.0

 - Update to [redis component 2.0.0](https://github.com/M6Web/Redis)
 - Option `disable_data_collector` no longer available
 - Option cache_resetter no longer available
 
## Upgrading to 3.0.0

 - Option `compress` no longer available
 - cache type only means you should have a namespace
 - multi mode (exec command on each server) is no longuer available
 - internal services are named via the m6web_redis pattern
 - configuration root node is m6web_redis