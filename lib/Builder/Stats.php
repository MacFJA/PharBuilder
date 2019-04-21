<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder;

use gugglegum\MemorySize\Formatter;
use webignition\ReadableDuration\Factory;
use webignition\ReadableDuration\ReadableDuration;

class Stats
{
    /** @var int */
    private $startTime = 0;
    /** @var int */
    private $endTime = -1;
    /** @var string */
    private $finalPath = '';

    public static function getSizeString(string $path): string
    {
        $parser = new Formatter();
        $parser->getOptions()->lazyInitialization();

        $size = @filesize($path);

        if ($size === false) {
            return 'error';
        }

        return $parser->format($size);
    }

    public static function getDurationString(int $durationInSec): string
    {
        $factory = new Factory();
        $duration = $factory->create($durationInSec);

        $data = $factory->getInMostAppropriateUnits($duration, 2);
        $result = [];
        foreach ($data as $unit) {
            $result[] = $unit['value'] . ' ' . $unit['unit'] . ($unit['value'] > 1 ? 's' : '');
        }

        return implode(', ', $result);
    }

    /** @codeCoverageIgnore */
    public function start(): void
    {
        $this->startTime = time();
    }

    /** @codeCoverageIgnore */
    public function end(): void
    {
        $this->endTime = time();
    }

    /** @codeCoverageIgnore */
    public function getFinalPath(): string
    {
        return $this->finalPath;
    }

    /** @codeCoverageIgnore */
    public function setFinalPath(string $finalPath): void
    {
        $this->finalPath = $finalPath;
    }

    /** @codeCoverageIgnore */
    public function getDuration(): int
    {
        return $this->endTime - $this->startTime;
    }
}
