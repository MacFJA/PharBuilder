<?php


namespace MacFJA\PharBuilder\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Base.
 * Contains all shared function for the getting all phar information
 *
 * @package MacFJA\PharBuilder\Commands
 * @author  MacFJA
 * @license MIT
 */
abstract class Base extends Command
{
    /**
     * The Symfony Style Input/Output
     *
     * @var SymfonyStyle
     */
    protected $ioStyle = null;

    /**
     * Set the Symfony Inout/Output style
     *
     * @param SymfonyStyle $ioStyle The Symfony Style Input/Output
     *
     * @return void
     */
    public function setIo($ioStyle)
    {
        $this->ioStyle = $ioStyle;
    }

    /**
     * Format code to be displayed in CLI help
     *
     * @param string $code The text to format
     *
     * @return string A formatted help text
     */
    protected function codeHelpParagraph($code)
    {
        $result = '  │<comment>  ' . str_replace(
            PHP_EOL,
            '</>' . PHP_EOL . '  │<comment>  ',
            $code
        ) . '</>';

        return '  ┌' . PHP_EOL . $result . PHP_EOL . '  └';
    }
}
