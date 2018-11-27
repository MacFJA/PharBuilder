<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder;

use MacFJA\PharBuilder\Builder\Compressor\CompressorInterface;

class ArchiveCompressor
{
    /** @var CompressorInterface */
    private $strategy;
    /** @var int */
    private $compression;
    /** @var \Phar */
    private $phar;

    /**
     * ArchiveCompressor constructor.
     *
     * @param CompressorInterface $strategy
     * @param int                 $compression
     * @param \Phar               $phar
     */
    public function __construct(\Phar $phar, CompressorInterface $strategy, int $compression)
    {
        $this->strategy = $strategy;
        $this->compression = $compression;
        $this->phar = $phar;
    }

    public function compress(): bool
    {
        return $this->strategy->execute($this->phar, $this->compression);
    }
}
