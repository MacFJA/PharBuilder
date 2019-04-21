<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\UnitTest\Options\Event;

use Gstarczyk\Mimic\Mimic;
use MacFJA\PharBuilder\Composer\ComposerJson;
use MacFJA\PharBuilder\Options\Event\PathBasedResultEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class PathBasedResultEventTest
 *
 * @covers MacFJA\PharBuilder\Options\Event\PathBasedResultEvent
 *
 * @package MacFJA\PharBuilder\tests\Options\Event
 */
class PathBasedResultEventTest extends TestCase
{

    public function testIsPathValid()
    {
        /** @var PathBasedResultEvent $mock */
        $mock = Mimic::mock(PathBasedResultEvent::class);
        Mimic::callConstructor($mock, '*', true, false, null, null);
        Mimic::spy($mock, 'isPathValid');

        $this->assertTrue($mock->isPathValid(__DIR__));
        $this->assertFalse($mock->isPathValid(__FILE__));
        $this->assertFalse($mock->isPathValid(__DIR__ . '/error/'));

        /** @var PathBasedResultEvent $mock */
        $mock = Mimic::mock(PathBasedResultEvent::class);
        Mimic::callConstructor($mock, '*', false, true, null, null);
        Mimic::spy($mock, 'isPathValid');

        $this->assertFalse($mock->isPathValid(__DIR__));
        $this->assertTrue($mock->isPathValid(__FILE__));
        $this->assertFalse($mock->isPathValid(__DIR__ . '/error'));
    }

    private function buildMock()
    {
        $mock = Mimic::mock(PathBasedResultEvent::class);
        Mimic::callConstructor($mock, ...func_get_args());
        return $mock;
    }

    public function testConstructorNoExpect()
    {
        try {
            $this->buildMock('*', false, false, null, null);
            self::fail();
        } catch (\InvalidArgumentException $e) {
            self::assertEquals('Both expectDirectory and expectFile can\'t be false', $e->getMessage());
        }
    }

    public function testGetComposerJsonDirectory()
    {
        $noComposer = $this->buildMock('*', true, true, null, null);
        Mimic::spy($noComposer, 'getComposerJsonDirectory');
        $this->assertEquals(null, $noComposer->getComposerJsonDirectory());

        /** @var ComposerJson $mock */
        $mock = Mimic::mock(ComposerJson::class);
        Mimic::when($mock)->invoke('isValid')->withoutArguments()->willReturn(false);
        $invalidComposer = $this->buildMock('*', true, true, $mock, null);
        $this->assertEquals(null, $invalidComposer->getComposerJsonDirectory());

        /** @var ComposerJson $mock */
        $mock = Mimic::mock(ComposerJson::class);
        Mimic::when($mock)->invoke('isValid')->withoutArguments()->willReturn(true);
        Mimic::when($mock)->invoke('getPath')->withoutArguments()->willReturn(__DIR__ . '/composer.json');
        /** @var PathBasedResultEvent $invalidComposer */
        $invalidComposer = $this->buildMock('*', true, true, $mock, null);
        Mimic::spy($invalidComposer, 'getComposerJsonDirectory');
        $this->assertEquals(__DIR__, $invalidComposer->getComposerJsonDirectory());
    }

    public function testGetCurrentWorkingDirectory()
    {
        $defaultCwd = $this->buildMock('*', true, true, null, null);
        Mimic::spy($defaultCwd, 'getCurrentWorkingDirectory');
        $this->assertEquals(getcwd(), $defaultCwd->getCurrentWorkingDirectory());

        $customCwd = $this->buildMock('*', true, true, null, __DIR__);
        Mimic::spy($customCwd, 'getCurrentWorkingDirectory');
        $this->assertEquals(__DIR__, $customCwd->getCurrentWorkingDirectory());
    }
}
