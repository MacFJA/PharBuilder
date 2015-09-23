<?php


namespace MacFJA\PharBuilder;

use MacFJA\PharBuilder\Commands\Build;
use MacFJA\PharBuilder\Commands\Package;
use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use Symfony\Component\Console\Application as Base;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application.
 * Expose commands, the main purpose of this class is to get all information to work.
 *
 * Exit code of the application are:
 *  - `0`: Success
 *  - `1`: Wrong *composer.json* file
 *  - `2`: Wrong entry point for the application (file does not exist)
 *  - `3`: Wrong output directory
 *  - `4`: The Phar filename is not valid
 *  - `5`: PHP ini wrongly setting up
 *  - `6`: Require input, but in no-interactive mode
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\PharBuilder
 */
class Application extends Base
{
    /**
     * Init the application and create the main and default command
     */
    public function __construct()
    {
        parent::__construct('MacFJA PharBuilder', '@dev');

        $this->getHelperSet()->set(new FilechooserHelper());

        // Add the full interactive command
        $this->add(new Build());
        // Add the Composer based command
        $this->add(new Package());

        /*
         * Set the build command the default command.
         * So without argument, instead of the help command, the build command will be launch
         */
        $this->setDefaultCommand('build');
    }

    /**
     * Check if the application can run properly in the current environment.
     * Exit code: `5`
     *
     * @param OutputInterface $output The CLI output interface (display message)
     */
    public function checkSystem(OutputInterface $output) {
        $pharReadonly = ini_get('phar.readonly');
        if ($pharReadonly === '1') {
            $this->renderException(new \Exception('The configuration of your PHP do not authorize PHAR creation. (see phar.readonly in you php.ini'), $output);
            exit(5);
        }

    }

    /**
     * Configure Output interface and do some checks
     *
     * @param InputInterface  $input  The CLI input interface (reading user input)
     * @param OutputInterface $output The CLI output interface (display message)
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);
        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('white', 'green'));

        $this->checkSystem($output);
    }
}