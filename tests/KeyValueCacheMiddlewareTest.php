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

namespace Drift\Tests;

use function Clue\React\Block\await;
use Drift\Cache\KeyValueCacheMiddleware;
use Drift\Cache\LocalKeyValueCache;
use function Drift\React\usleep as async_usleep;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use function React\Promise\reject;
use function React\Promise\resolve;

class KeyValueCacheMiddlewareTest extends TestCase
{
    public function testNotFoundCallback()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $middleware = new KeyValueCacheMiddleware($cache);
        $accesses = 0;
        $notFoundCallback = function (string $key) use (&$accesses) {
            ++$accesses;
            if ('exc' === $key) {
                throw new \Exception('exception');
            }

            if ('reject' === $key) {
                return reject(new \Exception('rejected'));
            }

            if ('resolve' === $key) {
                return resolve('resolved');
            }

            return "{$key}_val";
        };

        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback), $loop));
        $this->assertEquals(1, $accesses);
        $this->assertEquals('2_val', await($middleware->getOrAsk('2', $notFoundCallback), $loop));
        $this->assertEquals(2, $accesses);
        $this->assertEquals('resolved', await($middleware->getOrAsk('resolve', $notFoundCallback), $loop));
        $this->assertEquals(3, $accesses);
        $this->assertEquals('resolved', await($middleware->getOrAsk('resolve', $notFoundCallback), $loop));
        $this->assertEquals(3, $accesses);

        try {
            await($middleware->getOrAsk('reject', $notFoundCallback), $loop);
            $this->fail('Should fail');
        } catch (\Exception $exception) {
            $this->assertEquals('rejected', $exception->getMessage());
            $this->assertEquals(4, $accesses);
        }

        try {
            await($middleware->getOrAsk('reject', $notFoundCallback), $loop);
            $this->fail('Should fail');
        } catch (\Exception $exception) {
            $this->assertEquals('rejected', $exception->getMessage());
            $this->assertEquals(5, $accesses);
        }

        try {
            await($middleware->getOrAsk('exc', $notFoundCallback), $loop);
            $this->fail('Should fail');
        } catch (\Exception $exception) {
            $this->assertEquals('exception', $exception->getMessage());
            $this->assertEquals(6, $accesses);
        }

        try {
            await($middleware->getOrAsk('exc', $notFoundCallback), $loop);
            $this->fail('Should fail');
        } catch (\Exception $exception) {
            $this->assertEquals('exception', $exception->getMessage());
            $this->assertEquals(7, $accesses);
        }
    }

    public function testTTL()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $middleware = new KeyValueCacheMiddleware($cache);
        $accesses = 0;
        $notFoundCallback = function (string $key) use (&$accesses) {
            ++$accesses;

            return "{$key}_val";
        };

        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback, 0.05), $loop));
        $this->assertEquals(1, $accesses);
        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback, 0.05), $loop));
        $this->assertEquals(1, $accesses);
        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback, 0.05), $loop));
        $this->assertEquals(2, $accesses);

        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback, 0.05, true), $loop));
        $this->assertEquals(2, $accesses);

        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback, 0.05, true), $loop));
        $this->assertEquals(2, $accesses);

        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1_val', await($middleware->getOrAsk('1', $notFoundCallback, 0.05, true), $loop));
        $this->assertEquals(2, $accesses);
    }
}
