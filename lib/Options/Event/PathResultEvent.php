<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options\Event;

class PathResultEvent extends PathBasedResultEvent
{
    /** @var null|string */
    private $path;

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): bool
    {
        $this->path = null;

        if ($this->isPathValid($path)) {
            $this->path = $path;

            return true;
        }

        return false;
    }
}
