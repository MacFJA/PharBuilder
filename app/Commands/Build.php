<?php


namespace MacFJA\PharBuilder\Commands;

use MacFJA\PharBuilder\Application;
use MacFJA\PharBuilder\Event\ComposerAwareEvent;
use MacFJA\PharBuilder\Event\PharAwareEvent;
use MacFJA\PharBuilder\PharBuilder;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Build.
 * The command `build` of the application. This command is full interactive.
 *
 * @package MacFJA\PharBuilder\Commands
 * @author  MacFJA
 * @license MIT
 */
class Build extends Base
{
    /**
     * Configure the command (name and description)
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the name is invalid
     */
    protected function configure()
    {
        $this->setName('build')->setDescription('Full interactive Phar builder');
    }

    /**
     * Execute the command.
     * Exit codes: `0`, `1`, `2`, `3`, `4`, `6`, `7`, `8`
     *
     * @param InputInterface  $input  The CLI input interface (reading user input)
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return void
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \RuntimeException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) -- $output parameter inherited from parent class
     *///@codingStandardsIgnoreLine
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->isInteractive()) {
            $this->throwErrorForNoInteractiveMode();
        }

        /**
         * The application
         *
         * @var Application $app
         */
        $app = $this->getApplication();
        $app->getConfig()->setReadParam(false);
        $app->getConfig()->setReadExtra(false);

        $this->ioStyle->title('Configuring your Phar application...');

        // -- Ask for composer.json file (the base file of the project)
        $composerFile = realpath($app->getConfig()->getValue('composer'));
        $app->getBuilder()->setComposer($composerFile);

        $app->getConfig()->setComposerDir(dirname($composerFile));

        // -- Ask for the dev
        $app->getConfig()->getValue('include-dev');

        // -- Ask for the stub <=> the entry point of the application
        $app->getConfig()->getValue('entry-point');

        // -- Ask for the compression
        $app->getConfig()->getValue('compression');

        // -- Ask for the name of the phar file
        $app->getConfig()->getValue('name');

        // -- Ask for the output folder
        $app->getConfig()->getValue('output-dir');

        // -- Ask for the skip shebang flag
        $app->getConfig()->getValue('shebang');

        // -- Build the Phar
        $builder = $app->getBuilder();

        $app->emit(new PharAwareEvent('command.build.start', $builder));

        $builder->buildPhar();

        $app->emit(new ComposerAwareEvent('command.build.end', $builder->getComposerReader()));

        return null;
    }

    /**
     * Display an error that indicate that the application is in a no interactive mode and require an input.
     * Exit code: `6`
     *
     * @param string|null $missedOption The name of the missing option
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    protected function throwErrorForNoInteractiveMode($missedOption = null)
    {
        $message = 'The terminal set the application in a no-interactive mode.';
        if ($missedOption) {
            // @codingStandardsIgnoreLine
            $message .= ' Disable no-interactive mode or describe "' . $missedOption .'" ' .
                'in composer.json (ex. https://github.com/MacFJA/PharBuilder/blob/master/docs/ComposerExtra.md)';
        }
        $this->ioStyle->error($message);
        exit(6);
    }
}
