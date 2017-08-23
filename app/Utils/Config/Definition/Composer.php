<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

/**
 * Class Composer.
 * Read from the composer.json extra data.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class Composer implements Definition
{
    /**
     * The content of the extra data.
     *
     * @var array
     */
    protected $extraData;
    /**
     * The name of the data (key)
     *
     * @var string
     */
    protected $name;

    /**
     * Composer constructor.
     *
     * @param array  $extraData The content of the extra data.
     * @param string $name      The name of the data (key)
     */
    public function __construct($extraData, $name)
    {
        $this->extraData = $extraData;
        $this->name      = $name;
    }

    /**
     * Get the configuration value
     *
     * @return mixed|null
     */
    public function get()
    {
        if ($this->has()) {
            return $this->extraData[$this->name];
        }
        return null;
    }

    /**
     * Does this configuration reader have the value
     *
     * @return bool
     */
    public function has()
    {
        return array_key_exists($this->name, $this->extraData);
    }
}
