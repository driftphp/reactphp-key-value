<?php

/*
 * This file is part of the DriftPHP Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Drift\Cache;

use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class KeyValueCacheMiddleware
{
    private KeyValueCache $cache;

    /**
     * @param KeyValueCache $cache
     */
    public function __construct(KeyValueCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string     $key
     * @param callable   $notFoundCallback
     * @param float|null $ttl
     * @param bool       $refreshTTL
     *
     * @return PromiseInterface<mixed>
     */
    public function getOrAsk(
        string $key,
        callable $notFoundCallback,
        float $ttl = null,
        bool $refreshTTL = false
    ): PromiseInterface {
        if ($this->cache->has($key)) {
            return resolve($this->cache->get($key, $refreshTTL));
        }

        return resolve($notFoundCallback($key))
            ->then(function ($value) use ($key, $ttl) {
                $this->cache->set($key, $value, $ttl);

                return $value;
            });
    }

    /**
     * @return KeyValueCache
     */
    public function getCache(): KeyValueCache
    {
        return $this->cache;
    }
}
