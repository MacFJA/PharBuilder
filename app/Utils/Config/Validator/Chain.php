<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

/**
 * Class Chain.
 * It allow to setup a series of validator that must all validate the input.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
class Chain extends BaseValidator
{
    /**
     * List of validator to use.
     *
     * @var BaseValidator[]
     */
    protected $chain = array();

    /**
     * Add a validator in the chain
     *
     * @param BaseValidator $validator List of validator to use.
     * @param string|null   $before    <p>The FCQN of the class where to add the validator. If not provided or
     *                                 non-existent, the validator will be added at the end of the chain.</p>
     *
     * @return $this
     */
    public function addValidator($validator, $before = null)
    {
        if ($before !== null) {
            foreach ($this->chain as $index => $item) {
                if (get_class($item) === $before) {
                    array_splice($this->chain, $index, 0, array($validator));
                    return $this;
                }
            }
        }
        $this->chain[] = $validator;
        return $this;
    }

    /**
     * Do the actual validation
     *
     * @param mixed $value The value to check
     *
     * @return bool
     */
    protected function doValidation($value)
    {
        foreach ($this->chain as $validator) {
            if (!$validator->validate($value)) {
                return false;
            }
        }

        return true;
    }
}
