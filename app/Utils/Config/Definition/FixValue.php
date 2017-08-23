<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

/**
 * Class FixValue.
 * Return a predefined value
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class FixValue implements Definition
{
    /**
     * The value to return
     *
     * @var mixed
     */
    protected $value;

    /**
     * FixValue constructor.
     *
     * @param mixed $value The value to return
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the configuration value
     *
     * @return mixed|null
     */
    public function get()
    {
        return $this->value;
    }

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
