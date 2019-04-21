<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\UnitTest\Options;

use MacFJA\PharBuilder\Options\ConfigurationOptions;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurationOptionsTest
 *
 * @covers \MacFJA\PharBuilder\Options\ConfigurationOptions
 * @package MacFJA\PharBuilder\tests\Options
 */
class ConfigurationOptionsTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetName($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getName());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetExcluded($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getExcluded());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetOutputPath($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getOutputPath());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetIncluded($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getIncluded());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetCompression($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getCompression());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetStubPath($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getStubPath());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testGetEntryPoint($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->getEntryPoint());
    }
    /**
     * @dataProvider dataProvider
     * @param $configuration
     * @param $expected
     */
    public function testIncludeDev($configuration, $expected)
    {
        $confOptions = new ConfigurationOptions($configuration, __DIR__ . '/../fixtures/root');

        $this->assertEquals($expected, $confOptions->includeDev());
    }

    public function dataProvider($methodName)
    {
        if ($methodName === 'testGetName') {
            return [
                [[ConfigurationOptions::NAME_OPTION_NAME => 'hello'], 'hello'],
                [[ConfigurationOptions::NAME_OPTION_NAME => 'world'], 'world'],
                [['nope' => 'world'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetIncluded') {
            return [
                [[ConfigurationOptions::INCLUDED_OPTION_NAME => ['a-directory']], ['a-directory']],
                [[ConfigurationOptions::INCLUDED_OPTION_NAME => ['a-directory', 'a-file']], ['a-directory', 'a-file']],
                [[ConfigurationOptions::INCLUDED_OPTION_NAME => ['a-nope-directory', 'a-file']], ['a-file']],
                [[ConfigurationOptions::INCLUDED_OPTION_NAME => ['a-directory', 'a-nope-file']], ['a-directory']],
                [[ConfigurationOptions::INCLUDED_OPTION_NAME => ['a-nope-file']], null],
                [[ConfigurationOptions::INCLUDED_OPTION_NAME => []], null],
                [['nope' => 'world'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetExcluded') {
            return [
                [[ConfigurationOptions::EXCLUDED_OPTION_NAME => ['a-directory']], ['a-directory']],
                [[ConfigurationOptions::EXCLUDED_OPTION_NAME => ['a-directory', 'a-file']], ['a-directory', 'a-file']],
                [[ConfigurationOptions::EXCLUDED_OPTION_NAME => ['a-nope-directory', 'a-file']], ['a-nope-directory', 'a-file']],
                [[ConfigurationOptions::EXCLUDED_OPTION_NAME => ['a-directory', 'a-nope-file']], ['a-directory', 'a-nope-file']],
                [[ConfigurationOptions::EXCLUDED_OPTION_NAME => ['a-nope-file']], ['a-nope-file']],
                [[ConfigurationOptions::EXCLUDED_OPTION_NAME => []], []],
                [['nope' => 'world'], []],
                [[], []],
            ];
        } elseif ($methodName === 'testGetOutputPath') {
            return [
                [[ConfigurationOptions::OUTPUT_OPTION_NAME => '/'], '/'],
                [[ConfigurationOptions::OUTPUT_OPTION_NAME => './'], './'],
                [[ConfigurationOptions::OUTPUT_OPTION_NAME => '../'], '../'],
                [[ConfigurationOptions::OUTPUT_OPTION_NAME => 'a-directory'], 'a-directory'],
                [[ConfigurationOptions::OUTPUT_OPTION_NAME => 'a-file'], null],
                [[ConfigurationOptions::OUTPUT_OPTION_NAME => 'non-existing'], null],
                [['nope' => 'world'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetEntryPoint') {
            return [
                [[ConfigurationOptions::ENTRYPOINT_OPTION_NAME => '/'], null],
                [[ConfigurationOptions::ENTRYPOINT_OPTION_NAME => 'a-directory'], null],
                [[ConfigurationOptions::ENTRYPOINT_OPTION_NAME => 'a-file'], 'a-file'],
                [['nope' => 'world'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetCompression') {
            return [
                [[ConfigurationOptions::COMPRESSION_OPTION_NAME => ConfigurationOptions::BZ_COMPRESSION_OPTION_VALUE], \Phar::BZ2],
                [[ConfigurationOptions::COMPRESSION_OPTION_NAME => ConfigurationOptions::GZ_COMPRESSION_OPTION_VALUE], \Phar::GZ],
                [[ConfigurationOptions::COMPRESSION_OPTION_NAME => ConfigurationOptions::NO_COMPRESSION_OPTION_VALUE], \Phar::NONE],
                [[ConfigurationOptions::COMPRESSION_OPTION_NAME => 'nope'], null],
                [['nope' => 'world'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testIncludeDev') {
            $key = ConfigurationOptions::WITH_DEV_OPTION_NAME;
            return [
                [[$key => true], true],
                [[$key => 'true'], true],
                [[$key => 1], true],
                [[$key => 'false'], false],
                [[$key => false], false],
                [[$key => 0], false],
                [[$key => 'nope'], null],
                [['nope' => 'world'], null],
                [[], null],
            ];
        } elseif ($methodName === 'testGetStubPath') {
            return [
                [[ConfigurationOptions::WITH_SHEBANG_OPTION_NAME => true], \dirname(__DIR__, 3) . '/resources/stubs/map-phar+shebang.tpl'],
                [[ConfigurationOptions::WITH_SHEBANG_OPTION_NAME => false], \dirname(__DIR__, 3) . '/resources/stubs/map-phar+no-shebang.tpl'],
                [[ConfigurationOptions::WITH_SHEBANG_OPTION_NAME => 'error'], null],
                [[], null],
            ];
        }
        return [];
    }
}
