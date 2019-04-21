<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\UnitTest\Options;

use MacFJA\PharBuilder\Options\ConsoleOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ConsoleOptionsTest
 *
 * @covers \MacFJA\PharBuilder\Options\ConsoleOptions
 * @package MacFJA\PharBuilder\tests\Options
 */
class ConsoleOptionsTest extends TestCase
{
    private function buildOptions($config): ConsoleOptions
    {
        $input = new ArrayInput($config, ConsoleOptions::getInputDefinition());
        return new ConsoleOptions($input, __DIR__ . '/../fixtures/root');
    }

    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetName($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getName());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetExcluded($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getExcluded());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetOutputPath($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getOutputPath());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetIncluded($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getIncluded());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetCompression($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getCompression());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetStubPath($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getStubPath());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetEntryPoint($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->getEntryPoint());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testIncludeDev($configuration, $expected)
    {
        $this->assertEquals($expected, $this->buildOptions($configuration)->includeDev());
    }

    public function dataProvider($methodName)
    {
        if ($methodName === 'testGetName') {
            return [
                [['--' . ConsoleOptions::NAME_OPTION_NAME => 'hello'], 'hello'],
                [['--' . ConsoleOptions::NAME_OPTION_NAME => 'world'], 'world'],
                [[], null],
            ];
        } elseif ($methodName === 'testGetIncluded') {
            return [
                [['--' . ConsoleOptions::INCLUDED_OPTION_NAME => 'a-directory'], ['a-directory']],
                [['--' . ConsoleOptions::INCLUDED_OPTION_NAME => 'a-directory,a-file'], ['a-directory', 'a-file']],
                [['--' . ConsoleOptions::INCLUDED_OPTION_NAME => 'a-nope-directory,a-file'], ['a-file']],
                [['--' . ConsoleOptions::INCLUDED_OPTION_NAME => 'a-directory,a-nope-file'], ['a-directory']],
                [['--' . ConsoleOptions::INCLUDED_OPTION_NAME => 'a-nope-file'], null],
                [['--' . ConsoleOptions::INCLUDED_OPTION_NAME => ''], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetExcluded') {
            return [
                [['--' . ConsoleOptions::EXCLUDED_OPTION_NAME => 'a-directory'], ['a-directory']],
                [['--' . ConsoleOptions::EXCLUDED_OPTION_NAME => 'a-directory,a-file'], ['a-directory', 'a-file']],
                [['--' . ConsoleOptions::EXCLUDED_OPTION_NAME => 'a-nope-directory,a-file'], ['a-nope-directory','a-file']],
                [['--' . ConsoleOptions::EXCLUDED_OPTION_NAME => 'a-directory,a-nope-file'], ['a-directory','a-nope-file']],
                [['--' . ConsoleOptions::EXCLUDED_OPTION_NAME => 'a-nope-file'], ['a-nope-file']],
                [['--' . ConsoleOptions::EXCLUDED_OPTION_NAME => ''], []],
                [[], []],
            ];
        } elseif ($methodName === 'testGetOutputPath') {
            return [
                [['--' . ConsoleOptions::OUTPUT_OPTION_NAME => '/'], '/'],
                [['--' . ConsoleOptions::OUTPUT_OPTION_NAME => '../'], '../'],
                [['--' . ConsoleOptions::OUTPUT_OPTION_NAME => 'a-directory'], 'a-directory'],
                [['--' . ConsoleOptions::OUTPUT_OPTION_NAME => 'a-file'], null],
                [['--' . ConsoleOptions::OUTPUT_OPTION_NAME => 'non-existing'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetEntryPoint') {
            return [
                [['--' . ConsoleOptions::ENTRYPOINT_OPTION_NAME => '/'], null],
                [['--' . ConsoleOptions::ENTRYPOINT_OPTION_NAME => 'a-directory'], null],
                [['--' . ConsoleOptions::ENTRYPOINT_OPTION_NAME => 'a-file'], 'a-file'],
                [[], null],
            ];
        } elseif ($methodName === 'testGetCompression') {
            return [
                [['--' . ConsoleOptions::BZ_COMPRESSION_OPTION_NAME => true], \Phar::BZ2],
                [['--' . ConsoleOptions::GZ_COMPRESSION_OPTION_NAME => true], \Phar::GZ],
                [['--' . ConsoleOptions::NO_COMPRESSION_OPTION_NAME => true], \Phar::NONE],
                [[], null],
            ];
        } elseif ($methodName === 'testIncludeDev') {
            return [
                [['--' . ConsoleOptions::WITHOUT_DEV_OPTION_NAME => true], false],
                [['--' . ConsoleOptions::WITH_DEV_OPTION_NAME => true], true],
                [[], null],
            ];
        } elseif ($methodName === 'testGetStubPath') {
            return [
                [['--' . ConsoleOptions::WITHOUT_SHEBANG_OPTION_NAME => true], \dirname(__DIR__, 3) . '/resources/stubs/map-phar+no-shebang.tpl'],
                [['--' . ConsoleOptions::WITH_SHEBANG_OPTION_NAME => true], \dirname(__DIR__, 3) . '/resources/stubs/map-phar+shebang.tpl'],
                [[], null],
            ];
        }
        return [];
    }
}
