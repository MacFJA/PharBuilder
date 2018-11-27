<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Event;

use League\Event\Event;
use MacFJA\PharBuilder\Builder\ArchiveBuilder;

class BuilderEvent extends Event
{
    /** @var ArchiveBuilder */
    private $builder;

    /**
     * BuilderEvent constructor.
     *
     * @param string         $name
     * @param ArchiveBuilder $builder
     */
    public function __construct(string $name, ArchiveBuilder $builder)
    {
        parent::__construct($name);
        $this->builder = $builder;
    }

    /**
     * @return ArchiveBuilder
     */
    public function getBuilder(): ArchiveBuilder
    {
        return $this->builder;
    }
}
