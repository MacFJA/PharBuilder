<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options\Event;

/**
 * Class PathListResultEvent
 *
 * @package MacFJA\PharBuilder\Options\Event
 */
class PathListResultEvent extends PathBasedResultEvent
{
    /** @var string[]|null */
    private $path;

    public function getPaths(): ?array
    {
        return $this->path;
    }

    public function setPaths(array $path): bool
    {
        $this->path = [];

        if (count($path) === 0) {
            return true;
        }

        return array_reduce($path, function (bool $carry, string $item): bool {
            return $this->addPath($item) || $carry;
        }, false);
    }

    public function addPath(string $path): bool
    {
        if ($this->isPathValid($path)) {
            if ($this->path === null) {
                $this->path = [];
            }
            $this->path[] = $path;

            return true;
        }

        return false;
    }
}
