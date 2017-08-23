<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

/**
 * Class Callback.
 * Build a validator on a custom code.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
class Callback extends BaseValidator
{
    /**
     * The function/closure to execute
     *
     * @var callable
     */
    protected $closure;

    /**
     * Callback constructor.
     *
     * @param callable $callable The function/closure to execute
     */
    public function __construct(callable $callable)
    {
        $this->closure = $callable;
    }

    /**
     * Do the actual validation
     *
     * @param mixed $value The value to check
     *
     * @return bool
     */
    public function doValidation($value)
    {
        return call_user_func($this->closure, $value);
    }
}
