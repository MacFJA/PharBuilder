<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\tests\Options;

use MacFJA\PharBuilder\Options\DefaultOptions;
use PHPUnit\Framework\TestCase;

/**
 * Class DefaultOptionsTest
 *
 * @covers \MacFJA\PharBuilder\Options\DefaultOptions
 * @package MacFJA\PharBuilder\tests\Options
 */
class DefaultOptionsTest extends TestCase
{

    /**
     * @expectedException \BadMethodCallException
     */
    public function testGetName()
    {
        (new DefaultOptions())->getName();
    }

    public function testIncludeDev()
    {
        $this->assertFalse((new DefaultOptions())->includeDev());
    }

    public function testGetOutputPath()
    {
        $this->assertEquals(\dirname(getcwd()), (new DefaultOptions())->getOutputPath());
    }

    public function testGetCompression()
    {
        $this->assertEquals(\Phar::NONE, (new DefaultOptions())->getCompression());
    }

    public function testGetStubPath()
    {
        $this->assertEquals(
            \dirname(__DIR__, 2).'/resources/stubs/map-phar+shebang.tpl',
            (new DefaultOptions())->getStubPath()
        );
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testGetEntryPoint()
    {
        (new DefaultOptions())->getEntryPoint();
    }

    public function testGetIncluded()
    {
        $this->assertEquals([], (new DefaultOptions())->getIncluded());
    }

    public function testGetExcluded()
    {
        $this->assertEquals([], (new DefaultOptions())->getExcluded());
    }
}
