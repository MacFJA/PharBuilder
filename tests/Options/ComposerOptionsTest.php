<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\tests\Options;

use MacFJA\PharBuilder\Options\ComposerOptions;
use PHPUnit\Framework\TestCase;

/**
 * Class ComposerOptionsTest
 **@covers \MacFJA\PharBuilder\Options\ComposerOptions
 * @uses \MacFJA\PharBuilder\Composer\ComposerJson
 * @package MacFJA\PharBuilder\tests\Options
 */
class ComposerOptionsTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetOutputPath(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getOutputPath());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetExcluded(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getExcluded());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetStubPath(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getStubPath());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetName(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getName());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testIncludeDev(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->includeDev());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetEntryPoint(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getEntryPoint());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetIncluded(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getIncluded());
    }
    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param        $expected
     */
    public function testGetCompression(string $composerPath, $expected)
    {
        $composer = ComposerOptions::createFromPath($composerPath);
        $this->assertEquals($expected, $composer->getCompression());
    }

    public function dataProvider(string $testName)
    {
        if ($testName === 'testGetName') {
            return [
                [__DIR__.'/../fixtures/composer.json', 'polyfill-registry'],
                [__DIR__.'/../fixtures/composer2.json', null],
                [__DIR__.'/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testGetOutputPath') {
            return [
                [__DIR__.'/../fixtures/composer.json', __DIR__.'/../fixtures'],
                [__DIR__.'/../fixtures/composer2.json', __DIR__.'/../fixtures'],
                [__DIR__.'/../fixtures/composer.lock', __DIR__.'/../fixtures']
            ];
        } elseif ($testName === 'testGetStubPath') {
            return [
                [__DIR__.'/../fixtures/composer.json', null],
                [__DIR__.'/../fixtures/composer2.json', null],
                [__DIR__.'/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testIncludeDev') {
            return [
                [__DIR__.'/../fixtures/composer.json', null],
                [__DIR__.'/../fixtures/composer2.json', null],
                [__DIR__.'/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testGetEntryPoint') {
            return [
                [__DIR__.'/../fixtures/composer.json', 'root/a-file'],
                [__DIR__.'/../fixtures/composer2.json', null],
                [__DIR__.'/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testGetIncluded') {
            return [
                [__DIR__.'/../fixtures/composer.json', null],
                [__DIR__.'/../fixtures/composer2.json', null],
                [__DIR__.'/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testGetCompression') {
            return [
                [__DIR__.'/../fixtures/composer.json', null],
                [__DIR__.'/../fixtures/composer2.json', null],
                [__DIR__.'/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testGetExcluded') {
            return [
                [__DIR__.'/../fixtures/composer.json', []],
                [__DIR__.'/../fixtures/composer2.json', []],
                [__DIR__.'/../fixtures/composer.lock', []]
            ];
        }
        return [];
    }
}
