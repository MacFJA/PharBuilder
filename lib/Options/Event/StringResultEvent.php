<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options\Event;

use League\Event\Event;

/**
 * Class StringResultEvent
 *
 * @codeCoverageIgnore Simple Getter/Setter
 *
 * @package            MacFJA\PharBuilder\Options\Event
 */
class StringResultEvent extends Event
{
    /** @var string|null */
    private $result;

    /**
     * @return null|string
     */
    public function getResult(): ?string
    {
        return $this->result;
    }

    /**
     * @param null|string $result
     */
    public function setResult(?string $result): void
    {
        $this->result = $result;
    }
}
