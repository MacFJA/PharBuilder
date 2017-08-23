<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

/**
 * Class Chain
 * It allow to setup a fallback chain if the configuration reader/getter don't have the value.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class Chain implements Definition
{
    /**
     * List of configuration reader to use.
     *
     * @var Definition[]
     */
    protected $chain = array();

    /**
     * Get the configuration value
     *
     * @return mixed|null
     */
    public function get()
    {
        foreach ($this->chain as $item) {
            if ($item->has()) {
                $value = $item->get();
                if ($value === null) {
                    continue;
                }
                return $value;
            }
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
        foreach ($this->chain as $item) {
            if ($item->has()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a configuration reader in the chain
     *
     * @param Definition  $config The configuration reader
     * @param string|null $before <p>The FCQN of the class where to add the configuration. If not provided or
     *                             non-existent, the configuration will be added at the end of the chain.</p>
     *
     * @return $this
     */
    public function addConfig($config, $before = null)
    {
        if ($before !== null) {
            foreach ($this->chain as $index => $item) {
                if (get_class($item) === $before) {
                    array_splice($this->chain, $index, 0, array($config));
                    return $this;
                }
            }
        }
        $this->chain[] = $config;
        return $this;
    }
}
