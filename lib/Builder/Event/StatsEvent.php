<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Event;

use League\Event\Event;
use MacFJA\PharBuilder\Builder\Stats;

class StatsEvent extends Event
{
    /** @var Stats */
    private $stats;

    public function __construct(string $name, Stats $stats)
    {
        parent::__construct($name);
        $this->stats = $stats;
    }

    /**
     * @return Stats
     */
    public function getStats(): Stats
    {
        return $this->stats;
    }

    public function getDuration(): string
    {
        return Stats::getDurationString($this->stats->getDuration());
    }

    public function getSize(): string
    {
        return Stats::getSizeString($this->stats->getFinalPath());
    }
}
