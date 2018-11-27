<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options\Event;

use League\Event\Event;

/**
 * Class IntegerResultEvent
 *
 * @codeCoverageIgnore Simple Getter/Setter
 *
 * @package            MacFJA\PharBuilder\Options\Event
 */
class IntegerResultEvent extends Event
{
    /** @var int|null */
    private $result;

    public function getResult(): ?int
    {
        return $this->result;
    }

    public function setResult(?int $result): void
    {
        $this->result = $result;
    }
}
