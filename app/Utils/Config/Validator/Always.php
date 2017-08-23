<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

/**
 * Class Always.
 * A validator that always return that the value is valid.
 * (Its purpose is to provide a default validator)
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
class Always extends BaseValidator
{
    /**
     * Do the actual validation
     *
     * @param mixed $value The value to check
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) -- $value is not used as it's always valid
     */
    protected function doValidation($value)
    {
        return true;
    }
}
