<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Class SymfonyStyle.
 * Based on the callback Configuration definition, it will expose a SymfonyStyle helper to prompt to the user.
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class SymfonyStyle extends Callback
{
    /**
     * The CLI input interface (reading user input)
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Console constructor.
     *
     * @param InputInterface                                $input    The CLI input interface (reading user input)
     * @param \Symfony\Component\Console\Style\SymfonyStyle $ioStyle  The Input/Output helper
     * @param callable                                      $callback The function/closure to execute
     */
    public function __construct(
        InputInterface $input,
        \Symfony\Component\Console\Style\SymfonyStyle $ioStyle,
        callable $callback
    ) {
    

        $this->input = $input;
        parent::__construct($callback, array($ioStyle));
    }

    /**
     * Does this configuration reader have the value
     *
     * @return bool
     */
    public function has()
    {
        if (!$this->input->isInteractive()) {
            return false;
        }

        return true;
    }
}
