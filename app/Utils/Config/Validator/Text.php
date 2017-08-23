<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

/**
 * Class Text.
 * Validator on a string. It allow to check different type of properties
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
class Text extends BaseValidator
{
    /**
     * Check if the value contain a string
     */
    const CONTAIN = 0;
    /**
     * Check if the value start with a string
     */
    const START_WITH = 1;
    /**
     * Check if the value end a string
     */
    const END_WITH = 2;
    /**
     * Check if the value does not contain a string
     */
    const NO_CONTAIN = 3;
    /**
     * Check if the value does not start with a string
     */
    const NO_START_WITH = 4;
    /**
     * Check if the value does not end with a string
     */
    const NO_END_WITH = 5;
    /**
     * The validation type
     *
     * @var int
     */
    protected $operator;
    /**
     * The comparison string
     *
     * @var string
     */
    protected $needle;

    /**
     * Text constructor.
     *
     * @param int    $operator The validation type
     * @param string $needle   The comparison string
     */
    public function __construct($operator, $needle)
    {
        $this->operator = $operator;
        $this->needle   = $needle;
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
        switch ($this->operator) {
            case self::CONTAIN:
                return strpos($value, $this->needle) !== false;
            case self::START_WITH:
                return strpos($value, $this->needle) === 0;
            case self::END_WITH:
                return substr($value, -strlen($this->needle)) == $this->needle;
            case self::NO_CONTAIN:
                return strpos($value, $this->needle) === false;
            case self::NO_START_WITH:
                return strpos($value, $this->needle) !== 0;
            case self::NO_END_WITH:
                return substr($value, -strlen($this->needle)) != $this->needle;
            default:
                return false;
        }
    }
}
