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

interface KeyValueCache
{
    /**
     * @param string $key
     * @param bool   $refreshTTL
     *
     * @return mixed|null
     */
    public function get(string $key, bool $refreshTTL = false);

    /**
     * @param string     $key
     * @param mixed      $value
     * @param float|null $ttl
     *
     * @return void
     */
    public function set(string $key, $value, float $ttl = null): void;

    /**
     * @param string $key
     *
     * @return void
     */
    public function delete(string $key): void;
}
