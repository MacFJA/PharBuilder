<?php
/* Copyright (C) 2019 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\UnitTest\Composer;

use MacFJA\PharBuilder\Builder\Stats;
use MacFJA\PharBuilder\Composer\ComposerJson;
use PHPUnit\Framework\TestCase;

/**
 * Class StatsTest
 *
 * @covers \MacFJA\PharBuilder\Builder\Stats
 * @package MacFJA\PharBuilder\tests\Builder
 */
class StatsTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetSizeString(string $filePath, string $expected)
    {
        $this->assertEquals($expected, Stats::getSizeString($filePath));
    }
    /**
     * @dataProvider dataProvider
     */
    public function testGetDurationString(int $seconds, string $expected)
    {
        $this->assertEquals($expected, Stats::getDurationString($seconds));
    }

    public function dataProvider(string $testName)
    {
        if ($testName === 'testGetSizeString') {
            return [
                [__DIR__ . '/../fixtures/composer.json', '1006 B'],
                [__DIR__ . '/../fixtures/composer2.json', 'error'],
                [__DIR__ . '/../fixtures/error-composer/composer.json', '34 B'],
                [__DIR__ . '/../fixtures/composer.lock', '151.93 KiB']
            ];
        } elseif ($testName === 'testGetDurationString') {
            return [
                [0, '0 second'],
                [1, '1 second'],
                [10, '10 seconds'],
                [60, '1 minute'],
                [120, '2 minutes'],
                [125, '2 minutes, 5 seconds'],
                [3600, '1 hour'],
                [3660, '1 hour, 1 minute'],
                [3665, '1 hour, 1 minute'],
                [3605, '1 hour, 5 seconds'],
            ];
        }
        return [];
    }
}
