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
use Drift\Cache\LocalKeyValueCache;
use function Drift\React\usleep as async_usleep;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;

class LocalKeyValueCacheTest extends TestCase
{
    public function testGetNotFound()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $this->assertNull($cache->get('not-exists'));
    }

    public function testSetAndGet()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $cache->set('exists', 1);
        $this->assertNull($cache->get('not-exists'));
        $this->assertEquals('1', $cache->get('exists'));
    }

    public function testDelete()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $cache->set('exists', 1);
        $cache->delete('exists');
        $this->assertNull($cache->get('exists'));

        $cache->delete('not-exists');
        $this->assertNull($cache->get('not-exists'));
    }

    public function testSetWithTTL()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $cache->set('exists', 1, 0.05);
        $this->assertEquals('1', $cache->get('exists'));
        await(async_usleep(100000, $loop), $loop);
        $this->assertNull($cache->get('exists'));
    }

    public function testGetWithTTLUpdate()
    {
        $loop = Factory::create();
        $cache = new LocalKeyValueCache($loop);
        $cache->set('exists', 1, 0.05);
        $this->assertEquals('1', $cache->get('exists', true));
        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1', $cache->get('exists', true));
        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1', $cache->get('exists', true));
        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1', $cache->get('exists', true));
        await(async_usleep(30000, $loop), $loop);
        $this->assertEquals('1', $cache->get('exists', true));
        await(async_usleep(60000, $loop), $loop);
        $this->assertNull($cache->get('exists'));
    }
}
