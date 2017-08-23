<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class Argument.
 * Read argument from console command.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class Argument implements Definition
{
    /**
     * The CLI input interface (reading user input)
     *
     * @var InputInterface
     */
    protected $input;
    /**
     * The name of the argument to read
     *
     * @var string
     */
    protected $name;

    /**
     * Param constructor.
     *
     * @param InputInterface $input The CLI input interface (reading user input)
     * @param string         $name  The name of the argument to read
     */
    public function __construct(InputInterface $input, $name)
    {
        $this->input = $input;
        $this->name  = $name;
    }


    /**
     * Get the configuration value
     *
     * @return mixed|null
     */
    public function get()
    {
        try {
            return $this->input->getArgument($this->name);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * Does this configuration reader have the value
     *
     * @return bool
     */
    public function has()
    {
        try {
            return $this->input->hasArgument($this->name) !== null;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
