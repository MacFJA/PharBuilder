<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder;

use gugglegum\MemorySize\Formatter;
use webignition\ReadableDuration\ReadableDuration;

class Stats
{
    /** @var float */
    private $startTime;
    /** @var float */
    private $endTime;
    /** @var string */
    private $finalPath;

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

    public static function getDurationString($durationInSec): string
    {
        $duration = new ReadableDuration($durationInSec);

        $data = $duration->getInMostAppropriateUnits(2);
        $result = [];
        foreach ($data as $unit) {
            $result[] = $unit['value'] . ' ' . $unit['unit'] . ($unit['value'] > 1 ? 's' : '');
        }

        return implode(', ', $result);
    }

    public function start(): void
    {
        $this->startTime = time();
    }

    public function end(): void
    {
        $this->endTime = time();
    }

    /**
     * @return string
     */
    public function getFinalPath(): string
    {
        return $this->finalPath;
    }

    /**
     * @param string $finalPath
     */
    public function setFinalPath(string $finalPath): void
    {
        $this->finalPath = $finalPath;
    }

    public function getDuration(): int
    {
        return $this->endTime - $this->startTime;
    }
}
