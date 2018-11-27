<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\tests\Options\Event;

use MacFJA\PharBuilder\Options\Event\PathListResultEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class PathListResultEventTest
 *
 * @covers \MacFJA\PharBuilder\Options\Event\PathListResultEvent
 * @uses \MacFJA\PharBuilder\Options\Event\PathBasedResultEvent
 *
 * @package MacFJA\PharBuilder\tests\Options\Event
 */
class PathListResultEventTest extends TestCase
{

    public function testSetPaths()
    {
        $pathListResult = new PathListResultEvent('*', true, false, null, null);

        $this->assertNull($pathListResult->getPaths());

        $this->assertTrue($pathListResult->addPath(__DIR__));
        $this->assertEquals([__DIR__], $pathListResult->getPaths());

        $this->assertFalse($pathListResult->addPath(__FILE__));
        $this->assertEquals([__DIR__], $pathListResult->getPaths());

        $this->assertFalse($pathListResult->addPath(__DIR__.'/foobar/'));
        $this->assertEquals([__DIR__], $pathListResult->getPaths());

        $this->assertTrue($pathListResult->addPath(__DIR__.'/../../fixtures/root'));
        $this->assertEquals([__DIR__, __DIR__.'/../../fixtures/root'], $pathListResult->getPaths());
    }

    public function testAddPath()
    {
        $pathListResult = new PathListResultEvent('*', true, false, null, null);

        $this->assertTrue($pathListResult->setPaths([__DIR__, __DIR__.'/../../fixtures/root']));
        $this->assertEquals([__DIR__, __DIR__.'/../../fixtures/root'], $pathListResult->getPaths());

        $this->assertTrue($pathListResult->setPaths([]));
        $this->assertEmpty($pathListResult->getPaths());

        $this->assertFalse($pathListResult->setPaths([__FILE__]));
        $this->assertEmpty($pathListResult->getPaths());

        $this->assertTrue($pathListResult->setPaths([__FILE__, __DIR__]));
        $this->assertEquals([__DIR__], $pathListResult->getPaths());

        $this->assertTrue($pathListResult->setPaths([__DIR__.'/foobar/', __DIR__]));
        $this->assertEquals([__DIR__], $pathListResult->getPaths());
    }
}
