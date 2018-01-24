<?php


namespace MacFJA\PharBuilder\Commands;

use MacFJA\PharBuilder\Application;
use MacFJA\PharBuilder\Event\ComposerAwareEvent;
use MacFJA\PharBuilder\Event\PharAwareEvent;
use MacFJA\PharBuilder\Utils\Composer;
use MacFJA\PharBuilder\Utils\Config\Compression;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Package.
 * Command `package` of the application. Build the Phar application from 3 sources (in order of importance):
 *  - Console arguments
 *  - `composer.json` file
 *  - User input
 *
 * @package MacFJA\PharBuilder\Commands
 * @author  MacFJA
 * @license MIT
 */
class Package extends Base
{
    /**
     * Configure the command (name, arguments and descriptions)
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the name is invalid
     */
    protected function configure()
    {
        $resourcePath = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'resource'));
        $this->setName('package')
            ->addArgument(
                'composer',
                InputArgument::OPTIONAL,
                //@codingStandardsIgnoreStart
                'The path to the composer.json. If the argument is not provided, ' .
                'it will look for a composer.json file in the current directory'
                 //@codingStandardsIgnoreEnd
            )
            ->addOption('include-dev', '', InputOption::VALUE_NONE, 'Include development packages and path')
            ->addOption('entry-point', 'e', InputOption::VALUE_REQUIRED, 'Your application start file')
            ->addOption(
                'compression',
                '',
                InputOption::VALUE_REQUIRED,
                //@codingStandardsIgnoreStart
                'The compression of your Phar ' .
                '(possible values <option=bold>No</option=bold>, <option=bold>GZip</option=bold>, ' .
                '<option=bold>BZip2</option=bold>)'
                //@codingStandardsIgnoreEnd
            )
            ->addOption('no-compression', 'f', InputOption::VALUE_NONE, 'Do not compress the Phar')
            ->addOption('gzip', 'z', InputOption::VALUE_NONE, 'Set the compression of the Phar to GZip')
            ->addOption('bzip2', 'b', InputOption::VALUE_NONE, 'Set the compression of the Phar to BZip2')
            ->addOption('name', '', InputOption::VALUE_REQUIRED, 'The filename of the Phar archive')
            ->addOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'The output directory of the Phar archive')
            ->addOption(
                'include',
                'i',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'List of directories to add in Phar'
            )
            ->addOption('skip-shebang', 's', InputOption::VALUE_OPTIONAL, 'Skip the shebang')
            ->setHelp(
                file_get_contents($resourcePath . DIRECTORY_SEPARATOR . 'package-help.txt') .
                $this->codeHelpParagraph(
                    file_get_contents($resourcePath . DIRECTORY_SEPARATOR . 'package-extra-example.txt')?:''
                )
            )
            ->setDescription('Create a Phar file from a composer.json');
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) -- $output parameter inherited from parent class
     *///@codingStandardsIgnoreLine
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * The application
         *
         * @var Application $app
         */
        $app = $this->getApplication();
        $app->getConfig()->setReadParam(true);
        $composerFile = $app->getConfig()->getValue('composer');

        $composerFile = realpath($composerFile);
        $baseDir      = dirname($composerFile);
        chdir($baseDir);

        $app->getBuilder()->setComposer($composerFile);
        $composerReader = $app->getBuilder()->getComposerReader();
        $app->emit(new ComposerAwareEvent('command.package.start', $composerReader));

        /*
         * Read the composer.json file.
         * All information we need is store in it.
         */
        $extraData = $composerReader->getComposerConfig();
        $app->getConfig()
            ->setComposerDir($baseDir)
            ->setComposerReader($composerReader)
            ->setComposerExtra($extraData);

        $this->readSpecialParams($input);


        $builder = $app->getBuilder();
        $builder->setComposer($composerFile);

        $builder->buildPhar();

        $app->emit(new ComposerAwareEvent('command.package.end', $builder->getComposerReader()));

        return null;
    }

    /**
     * Read and transform special option
     *
     * @param InputInterface $input The CLI input interface (reading user input)
     *
     * @return void
     */
    protected function readSpecialParams(InputInterface $input)
    {
        if ($input->hasParameterOption('-z') || $input->hasParameterOption('--gzip')) {
            $input->setOption('compression', Compression::GZIP);
        } elseif ($input->hasParameterOption('-b') || $input->hasParameterOption('--bzip2')) {
            $input->setOption('compression', Compression::BZIP2);
        } elseif ($input->hasParameterOption('-f') || $input->hasParameterOption('--no-compression')) {
            $input->setOption('compression', Compression::NO);
        }
    }
}
