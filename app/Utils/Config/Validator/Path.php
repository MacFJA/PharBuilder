<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

/**
 * Class Path.
 * Validator on a path. It allow to check different type of properties
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
class Path extends BaseValidator
{
    /**
     * The validation is on the writing ability
     */
    const IS_WRITABLE = 0;
    /**
     * The validation is about type (directory)
     */
    const IS_DIR = 1;
    /**
     * The validation is about type (file)
     */
    const IS_FILE = 2;
    /**
     * Check if the file exist
     */
    const EXIST = 3;
    /**
     * Check if the directory exist, and its parent too
     */
    const HIERARCHY_EXIST = 4;
    /**
     * The validation to do
     *
     * @var int
     */
    protected $toCheck;

    /**
     * Path constructor.
     *
     * @param int $toCheck The validation to do
     */
    public function __construct($toCheck)
    {
        $this->toCheck = $toCheck;
    }

    /**
     * Shorthand
     *
     * @return static
     */
    public static function newExist()
    {
        return new static(self::EXIST);
    }

    /**
     * Shorthand
     *
     * @return static
     */
    public static function newIsDir()
    {
        return new static(self::IS_DIR);
    }

    /**
     * Shorthand
     *
     * @return static
     */
    public static function newIsFile()
    {
        return new static(self::IS_FILE);
    }

    /**
     * Shorthand
     *
     * @return static
     */
    public static function newWritable()
    {
        return new static(self::IS_WRITABLE);
    }

    /**
     * Shorthand
     *
     * @return static
     */
    public static function newHierarchyExist()
    {
        return new static(self::HIERARCHY_EXIST);
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
        switch ($this->toCheck) {
            case self::EXIST:
                return file_exists($value);
            case self::IS_DIR:
                return is_dir($value);
            case self::IS_FILE:
                return is_file($value);
            case self::IS_WRITABLE:
                return is_writable($value);
            case self::HIERARCHY_EXIST:
                return (file_exists($value) && is_dir($value) || mkdir($value, 0755, true));
            default:
                return false;
        }
    }
}
