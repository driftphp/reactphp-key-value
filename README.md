# ReactPHP key-value cache

[![CircleCI](https://circleci.com/gh/driftphp/reactphp-cache.svg?style=svg)](https://circleci.com/gh/driftphp/reactphp-cache)

Just a simple key-value local cache for your [ReactPHP](https://reactphp.org/)
projects

### Set a key

You can set a value given a key in this cache. As simple as it sounds.

```php
use React\EventLoop\Factory;
use Drift\Cache\LocalKeyValueCache;

$loop = Factory::create();
$cache = new LocalKeyValueCache($loop);
$cache->set('my_key', 'Any value');
```

### Define TTL

You can add a **TTL** for this key. After *n* seconds, this key will be
automatically deleted.

```php
use React\EventLoop\Factory;
use Drift\Cache\LocalKeyValueCache;

$loop = Factory::create();
$ttl = 0.1; // Means 0.1 second (100 milliseconds)
$cache = new LocalKeyValueCache($loop);
$cache->set('my_key', 'Any value', $ttl);
```

### Get a key

You can get a value from this cache by using the key. If the value is present
inside the cache, this one will be returned with no transformations. Otherwise,
null will be returned.

```php
use React\EventLoop\Factory;
use Drift\Cache\LocalKeyValueCache;

$loop = Factory::create();
$cache = new LocalKeyValueCache($loop);
$cache->set('my_key', 'Any value');
$value = $cache->get('my_key');
```

### Refresh TTL on access

TTL can offer a proper way to basically clean elements that are almost not
used. By defining a value in TTL, by default this key will be deleted after
*n* seconds, no matter how many times this key has been requested until this
moment. If we want to automatically refresh this TTL each time we access to a
key, we can use this feature.

In this example, we can see that the key is defined with a **TTL** of 2 seconds
and is requested each second, enabling the `refreshTTL` flag. In normal 
circumstances, after 2 seconds the `get` method should return null, but because
we are forcing the cache to refresh this TTL when we access the key, as long as
we don't have time gaps larger than 2 seconds, our key will always be available.

```php
use React\EventLoop\Factory;
use Drift\Cache\LocalKeyValueCache;

$loop = Factory::create();
$cache = new LocalKeyValueCache($loop);
$ttl = 2; // Means 2 seconds
$cache->set('my_key', 'Any value');

// ... After 1 second
$value = $cache->get('my_key', true); // Found

// ... After 1 second
$value = $cache->get('my_key', true); // Found

// ... After 1 second
$value = $cache->get('my_key', true); // Found

// ... After 3 second
$value = $cache->get('my_key', true); // Not Found
```

With this strategy you will only save locally these values used most frequent,
finding this way a nice equilibrium between cache efficiency and storage size.

### Delete a key

You can manually delete a key. If the key is not found inside the cache, nothing
will happen.

```php
use React\EventLoop\Factory;
use Drift\Cache\LocalKeyValueCache;

$loop = Factory::create();
$cache = new LocalKeyValueCache($loop);
$cache->delete('my_key');
```
