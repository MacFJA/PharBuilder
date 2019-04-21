<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\UnitTest\Composer;

use MacFJA\PharBuilder\Composer\ComposerJson;
use PHPUnit\Framework\TestCase;

/**
 * Class ComposerJsonTest
 *
 * @covers \MacFJA\PharBuilder\Composer\ComposerJson
 * @package MacFJA\PharBuilder\tests\Composer
 */
class ComposerJsonTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetName(string $composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $this->assertEquals($expected, $composer->getName());
    }
    /**
     * @dataProvider dataProvider
     */
    public function testGetPath(string $composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $this->assertEquals($expected, $composer->getPath());
    }
    /**
     * @dataProvider dataProvider
     */
    public function testGetBins(string $composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $this->assertEquals($expected, $composer->getBins());
    }
    /**
     * @dataProvider dataProvider
     */
    public function testGetVendorDir(string $composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $this->assertEquals($expected, $composer->getVendorDir());
    }
    /**
     * @dataProvider dataProvider
     */
    public function testIsValid(string $composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $this->assertEquals($expected, $composer->isValid());
    }

    /**
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param string $key
     * @param        $expected
     */
    public function testGetExtra(string $composerPath, string $key, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $this->assertEquals($expected, $composer->getExtra($key));
    }

    /**
     * @covers \MacFJA\PharBuilder\Composer\ComposerJson::getSourcesPath
     * @covers \MacFJA\PharBuilder\Composer\ComposerJson::getAutoloadsPath
     * @dataProvider dataProvider
     * @param string $composerPath
     * @param string $key
     * @param        $expected
     */
    public function testGetSourcesPath(string $composerPath, bool $includeDev, array $expected)
    {
        $composer = new ComposerJson($composerPath);

        $this->assertEquals($expected, $composer->getSourcesPath($includeDev));
    }

    public function dataProvider(string $testName)
    {
        if ($testName === 'testGetName') {
            return [
                [__DIR__ . '/../fixtures/composer.json', 'macfja/polyfill-registry'],
                [__DIR__ . '/../fixtures/composer2.json', null],
                [__DIR__ . '/../fixtures/error-composer/composer.json', null],
                [__DIR__ . '/../fixtures/composer.lock', null]
            ];
        } elseif ($testName === 'testGetPath') {
            return [
                [__DIR__ . '/../fixtures/composer.json', __DIR__ . '/../fixtures/composer.json'],
                [
                    __DIR__ . '/../fixtures/error-composer/composer.json',
                    __DIR__ . '/../fixtures/error-composer/composer.json'
                ],
                [__DIR__ . '/../fixtures/composer2.json', __DIR__ . '/../fixtures/composer2.json'],
                [__DIR__ . '/../fixtures/composer.lock', __DIR__ . '/../fixtures/composer.lock']
            ];
        } elseif ($testName === 'testGetBins') {
            return [
                [__DIR__ . '/../fixtures/composer.json', ['root/a-file']],
                [__DIR__ . '/../fixtures/composer2.json', []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', []],
                [__DIR__ . '/../fixtures/composer.lock', []]
            ];
        } elseif ($testName === 'testGetVendorDir') {
            return [
                [__DIR__ . '/../fixtures/composer.json', 'vendor'],
                [__DIR__ . '/../fixtures/composer2.json', 'vendor'],
                [__DIR__ . '/../fixtures/composer.lock', 'vendor'],
                [__DIR__ . '/../fixtures/error-composer/composer.json', 'vendor'],
                [__DIR__ . '/../fixtures/root/composer.json', 'a-directory']
            ];
        } elseif ($testName === 'testIsValid') {
            return [
                [__DIR__ . '/../fixtures/composer.json', true],
                [__DIR__ . '/../fixtures/composer2.json', false],
                [__DIR__ . '/../fixtures/composer.lock', false],
                [__DIR__ . '/../fixtures/error-composer/composer.json', false],
                [__DIR__ . '/../fixtures/root/composer.json', true]
            ];
        } elseif ($testName === 'testGetExtra') {
            return [
                [__DIR__ . '/../fixtures/composer.json', 'phar-builder', []],
                [__DIR__ . '/../fixtures/composer2.json', 'phar-builder', []],
                [__DIR__ . '/../fixtures/composer.lock', 'phar-builder', []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', 'phar-builder', []],
                [__DIR__ . '/../fixtures/root/composer.json', 'phar-builder', ['this-is-a-test']]
            ];
        } elseif ($testName === 'testGetSourcesPath') {
            return [
                [__DIR__ . '/../fixtures/composer.json', true, [__DIR__ . '/../fixtures/lib/']],
                [__DIR__ . '/../fixtures/composer.json', false, [__DIR__ . '/../fixtures/lib/']],
                [__DIR__ . '/../fixtures/composer2.json', true, []],
                [__DIR__ . '/../fixtures/composer.lock', false, []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', true, []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', false, []],
                [__DIR__ . '/../fixtures/root/composer.json', true, []]
            ];
        }
        return [];
    }
}
