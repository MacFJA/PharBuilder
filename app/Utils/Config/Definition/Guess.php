<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

/**
 * Class Guess.
 * Based on the callback Configuration definition, it always have a value.
 * The idea is that the callback function will make any assumption necessary to have a value, but can still result to a
 * `null` value if even with all assumptions it can find a value.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class Guess extends Callback
{
    /**
     * Does this configuration reader have the value
     *
     * @return bool
     */
    public function has()
    {
        return true;
    }
}
