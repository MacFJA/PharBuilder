<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options\Event;

use League\Event\Event;

/**
 * Class BooleanResultEvent
 *
 * @codeCoverageIgnore Simple Getter/Setter
 *
 * @package            MacFJA\PharBuilder\Options\Event
 */
class BooleanResultEvent extends Event
{
    /** @var bool|null */
    private $result;

    /**
     * @return null|bool
     */
    public function getResult(): ?bool
    {
        return $this->result;
    }

    /**
     * @param null|bool $result
     */
    public function setResult(?bool $result): void
    {
        $this->result = $result;
    }
}
