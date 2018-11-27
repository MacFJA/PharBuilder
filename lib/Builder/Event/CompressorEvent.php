<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Event;

use League\Event\Event;
use MacFJA\PharBuilder\Builder\ArchiveCompressor;

class CompressorEvent extends Event
{
    /** @var ArchiveCompressor */
    private $compressor;

    public function __construct(string $name, ArchiveCompressor $compressor)
    {
        parent::__construct($name);
        $this->compressor = $compressor;
    }

    /**
     * @return ArchiveCompressor
     */
    public function getCompressor(): ArchiveCompressor
    {
        return $this->compressor;
    }
}
