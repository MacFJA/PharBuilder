<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

/**
 * Interface Definition.
 * Describe how the a configuration value.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
interface Definition
{
    /**
     * Get the configuration value
     *
     * @return mixed|null
     */
    public function get();

    /**
     * Does this configuration reader have the value
     *
     * @return bool
     */
    public function has();
}
