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

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

/**
 * Class LocalKeyValueCache.
 */
class LocalKeyValueCache implements KeyValueCache
{
    private LoopInterface $loop;
    private array $cache = [];

    /**
     * @var TimerInterface[]
     */
    private array $timers = [];

    /**
     * @var int[]
     */
    private array $ttls = [];

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param string $key
     * @param bool   $refreshTTL
     *
     * @return mixed|null
     */
    public function get(string $key, bool $refreshTTL = false)
    {
        if (
            $refreshTTL &&
            array_key_exists($key, $this->ttls)
        ) {
            $timer = $this->timers[$key];
            $this->loop->cancelTimer($timer);
            $this->putTTLTimer($key, $this->ttls[$key]);
        }

        return $this->cache[$key] ?? null;
    }

    /**
     * @param string     $key
     * @param mixed      $value
     * @param float|null $ttl
     *
     * @return void
     */
    public function set(string $key, $value, float $ttl = null): void
    {
        $this->cache[$key] = $value;

        if ($ttl > 0) {
            $this->ttls[$key] = $ttl;
            $this->putTTLTimer($key, $ttl);
        }
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void
    {
        unset($this->cache[$key]);

        if (array_key_exists($key, $this->ttls)) {
            unset($this->ttls[$key]);
            $timer = $this->timers[$key];
            $this->loop->cancelTimer($timer);
            unset($this->timers[$key]);
        }
    }

    /**
     * @param string $key
     * @param float  $ttl
     */
    private function putTTLTimer(string $key, float $ttl)
    {
        $this->timers[$key] = $this
            ->loop
            ->addTimer($ttl, function () use ($key) {
                $this->delete($key);
            });
    }
}
