<?php

namespace MacFJA\PharBuilder\Utils\Config\Validator;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BaseValidator.
 * Act as a definition of capacity of a validator.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Validator
 * @author  MacFJA
 * @license MIT
 */
abstract class BaseValidator
{
    /**
     * The `exit` code
     *
     * @var int|null
     */
    protected $errorCode;
    /**
     * List of message to display if an error occur
     *
     * @var string[]
     */
    protected $errorMessages = array();
    /**
     * The Input/Output helper
     *
     * @var SymfonyStyle|null
     */
    protected $ioStyle;

    /**
     * Set the exit code number
     *
     * @param int $errorCode The `exit` code
     *
     * @return BaseValidator
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * Set the list of error message to display if an error occur.
     *
     * Each message can contain a `%s` sequence, it will be replace by the tested value.
     *
     * @param \string[] $errorMessages List of message to display if an error occur
     *
     * @return BaseValidator
     */
    public function setErrorMessages($errorMessages)
    {
        $this->errorMessages = $errorMessages;
        return $this;
    }

    /**
     * Set the Input/Output helper
     *
     * @param SymfonyStyle $ioStyle The Input/Output helper
     *
     * @return BaseValidator
     */
    public function setIoStyle($ioStyle)
    {
        $this->ioStyle = $ioStyle;
        return $this;
    }

    /**
     * Test if the value is valid
     *
     * @param mixed $value The value to validate.
     *
     * @return bool `true` if the value is correct
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    public function validate($value)
    {
        $result = $this->doValidation($value);

        if ($result) {
            return true;
        }

        if ($this->ioStyle != null && count($this->errorMessages)) {
            $this->ioStyle->error($this->getErrorMessages($value));
        }

        if ($this->errorCode) {
            exit($this->errorCode);
        }

        return false;
    }

    /**
     * Do the actual validation
     *
     * @param mixed $value The value to check
     *
     * @return bool
     */
    abstract protected function doValidation($value);

    /**
     * Inject in the error messages the value
     *
     * @param mixed $value The tested value (implicitly cast into a string)
     *
     * @return array
     */
    private function getErrorMessages($value)
    {
        return array_map(
            /**
             * Inject value into message
             *
             * @param string $message The message where to inject
             *
             * @return string
             */
            function ($message) use ($value) {
                return sprintf($message, $value);
            },
            $this->errorMessages
        );
    }
}
