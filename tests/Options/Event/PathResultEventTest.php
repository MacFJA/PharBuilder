<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\tests\Options\Event;

use MacFJA\PharBuilder\Options\Event\PathResultEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class PathResultEventTest
 *
 * @covers \MacFJA\PharBuilder\Options\Event\PathResultEvent
 * @uses \MacFJA\PharBuilder\Options\Event\PathBasedResultEvent
 *
 * @package MacFJA\PharBuilder\tests\Options\Event
 */
class PathResultEventTest extends TestCase
{

    public function testSetPath()
    {
        $fixtureDir = __DIR__.'/../../fixtures/root';
        $pathResult = new PathResultEvent('*', true, true, null, null);

        $this->assertFalse($pathResult->setPath('foo/bar'));
        $this->assertTrue($pathResult->setPath($fixtureDir.'/a-file'));
        $this->assertTrue($pathResult->setPath($fixtureDir.'/a-directory'));

        $pathResult = new PathResultEvent('*', false, true, null, null);
        $this->assertTrue($pathResult->setPath($fixtureDir.'/a-file'));
        $this->assertEquals($fixtureDir.'/a-file', $pathResult->getPath());
        $this->assertFalse($pathResult->setPath($fixtureDir.'/a-directory'));
        $this->assertNull($pathResult->getPath());

        $pathResult = new PathResultEvent('*', true, false, null, null);
        $this->assertFalse($pathResult->setPath($fixtureDir.'/a-file'));
        $this->assertNull($pathResult->getPath());
        $this->assertTrue($pathResult->setPath($fixtureDir.'/a-directory'));
        $this->assertEquals($fixtureDir.'/a-directory', $pathResult->getPath());
    }

    public function testConstructorNoExpect()
    {
        try {
            new PathResultEvent('*', false, false, null, null);
            self::fail();
        } catch (\InvalidArgumentException $e) {
            self::assertEquals('Both expectDirectory and expectFile can\'t be false', $e->getMessage());
        }
    }
}
