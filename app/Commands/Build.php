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
     * Exit codes: `0`, `1`, `2`, `3`, `4`, `6`
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
     */
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

        $this->ioStyle->title('Configuring your Phar application...');

        // -- Ask for composer.json file (the base file of the project)
        $composerFile = $this->askComposer($input, $output);
        $app->getBuilder()->setComposer($composerFile);

        // -- Ask for the dev
        $keepDev = $this->askIncludeDev();

        // -- Ask for the stub <=> the entry point of the application
        $stubFile = $this->askEntryPoint($input, $output, dirname($composerFile));

        // -- Ask for the compression
        $compression = $this->askCompression();

        // -- Ask for the name of the phar file
        $name = $this->askPharName();

        // -- Ask for the output folder
        $outputDir = $this->askOutputDir($input, $output, dirname($composerFile));
        
        // -- Ask for the skip shebang flag
        $skipShebang = $this->askSkipShebang();

        // -- Build the Phar
        $builder = $app->getBuilder();
        $builder->setOutputDir($outputDir);
        $builder->setPharName($name);
        $builder->setStubFile($stubFile);
        $builder->setCompression($compression);
        $builder->setKeepDev($keepDev);
        $builder->setSkipShebang($skipShebang);

        $app->emit(new PharAwareEvent('command.build.start', $builder));

        $builder->buildPhar();

        $app->emit(new ComposerAwareEvent('command.build.end', $builder->getComposerReader()));
    }
}
