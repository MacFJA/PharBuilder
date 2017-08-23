<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

/**
 * Class Callback.
 * Get a value from a custom code.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
abstract class Callback implements Definition
{
    /**
     * The function/closure to execute
     *
     * @var callable
     */
    protected $closure;
    /**
     * The list of params to use with the function/closure
     *
     * @var array
     */
    protected $params;

    /**
     * Callback constructor.
     *
     * @param callable $callable The function/closure to execute
     * @param array    $params   The list of params to use with the function/closure
     */
    public function __construct(callable $callable, array $params = array())
    {
        $this->closure = $callable;
        $this->params  = $params;
    }

    /**
     * Get the value by calling the callback
     *
     * @return mixed|null
     */
    public function get()
    {
        if ($this->has()) {
            return call_user_func_array($this->closure, $this->params);
        }
        return null;
    }
}
