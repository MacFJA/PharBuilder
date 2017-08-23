<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

/**
 * Class FixList.
 * List of predefined value that are valid.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
class FixList extends BaseValidator
{
    /**
     * List of allow values
     *
     * @var array
     */
    protected $allowed;

    /**
     * FixList constructor.
     *
     * @param array $allowed List of allow values
     */
    public function __construct($allowed)
    {
        $this->allowed = $allowed;
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
        return in_array($value, $this->allowed, true);
    }
}
