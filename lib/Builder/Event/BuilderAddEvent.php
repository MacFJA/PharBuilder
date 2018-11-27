<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Event;

use League\Event\AbstractEvent;

class BuilderAddEvent extends AbstractEvent
{
    /** @var string */
    private $path;
    /** @var bool */
    private $isDir;
    /** @var bool */
    private $isEmpty;

    /**
     * BuilderAddEvent constructor.
     *
     * @param string $path
     * @param bool   $isDir
     * @param bool   $isEmpty
     */
    public function __construct(string $path, bool $isDir, bool $isEmpty)
    {
        $this->path = $path;
        $this->isDir = $isDir;
        $this->isEmpty = $isEmpty;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isDir(): bool
    {
        return $this->isDir;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }


    public function getName()
    {
        return 'builder.add';
    }
}
