<?php


namespace MacFJA\PharBuilder\Commands;

use MacFJA\PharBuilder\Application;
use MacFJA\PharBuilder\Event\ComposerAwareEvent;
use MacFJA\PharBuilder\Event\PharAwareEvent;
use MacFJA\PharBuilder\Utils\Composer;
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
                    file_get_contents($resourcePath . DIRECTORY_SEPARATOR . 'package-extra-example.txt')
                )
            )
            ->setDescription('Create a Phar file from a composer.json');
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerFile = $input->getArgument('composer');
        if (null === $composerFile) {
            $composerFile = getcwd() . DIRECTORY_SEPARATOR . 'composer.json';
        }

        $this->validateComposer($composerFile);

        $composerFile = realpath($composerFile);
        $baseDir      = dirname($composerFile);
        chdir($baseDir);

        /**
         * The application
         *
         * @var Application $app
         */
        $app = $this->getApplication();
        $app->getBuilder()->setComposer($composerFile);
        $composerReader = $app->getBuilder()->getComposerReader();
        $app->emit(new ComposerAwareEvent('command.package.start', $composerReader));

        /*
         * Read the composer.json file.
         * All information we need is store in it.
         */
        $extraData = $composerReader->getComposerConfig();

        $this->readSpecialParams($input);

        $keepDev     = $this->readParamComposerIncludeDev($extraData, $input);
        $stubFile    = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'entry-point');
        $compression = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'compression');
        $name        = $this->readParamComposerAskName($extraData, $input);
        $outputDir   = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'output-dir');
        $includes    = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'include');
        $skipShebang = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'skip-shebang');

        $builder = $app->getBuilder();
        $builder->setComposer($composerFile);
        $builder->setOutputDir($outputDir);
        $builder->setPharName($name);
        $builder->setStubFile($stubFile);
        $builder->setCompression($compression);
        $builder->setIncludes($includes);
        $builder->setKeepDev($keepDev);
        $builder->setSkipShebang($skipShebang);

        $builder->buildPhar();

        $app->emit(new ComposerAwareEvent('command.package.end', $builder->getComposerReader()));
    }

    /**
     * Read the CLI argument,
     * if not found read the Composer.json file,
     * if not provided in composer.json, ask the user
     *
     * @param array           $composerData The composer.json extra data content
     * @param InputInterface  $input        The CLI input interface (reading user input)
     * @param OutputInterface $output       The CLI output interface (display message)
     * @param string          $baseDir      The path to the directory that contains the composer.json file
     * @param string          $dataName     The name of the data (hyphen word separated)
     *
     * @return mixed The value of `$dataName`
     */
    protected function readParamComposerAsk(
        $composerData,
        InputInterface $input,
        OutputInterface $output,
        $baseDir,
        $dataName
    ) {
        if (null == $input->getOption($dataName)) {
            if (array_key_exists($dataName, $composerData)) {
                $input->setOption($dataName, $composerData[$dataName]);
            } else {
                if (!$input->isInteractive()) {
                    $this->throwErrorForNoInteractiveMode($dataName);
                }
                $input->setOption(
                    $dataName,
                    call_user_func_array(
                        array($this, $this->getFunctionName('ask', $dataName)),
                        array($input, $output, $baseDir)
                    )
                );
            }
        }
        $data = call_user_func_array(
            array($this, $this->getFunctionName('validate', $dataName)),
            array($input->getOption($dataName))
        );

        return $data;
    }

    /**
     * Read the CLI argument for the phar name,
     * if not found read the Composer.json file,
     * if not provided in composer.json, ask the user
     *
     * @param array          $composerData The composer.json extra data content
     * @param InputInterface $input        The CLI input interface (reading user input)
     *
     * @return mixed The name of the phar
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function readParamComposerAskName($composerData, InputInterface $input)
    {
        if (null == $input->getOption('name')) {
            if (array_key_exists('name', $composerData)) {
                $input->setOption('name', $composerData['name']);
            } else {
                if (!$input->isInteractive()) {
                    $this->throwErrorForNoInteractiveMode('name');
                }
                $input->setOption('name', $this->askPharName());
            }
        }
        return $this->validatePharName($input->getOption('name'));
    }

    /**
     * Read the CLI argument for the including dev code and dev packages,
     * if not found read the Composer.json file.
     *
     * @param array          $composerData The composer.json extra data content
     * @param InputInterface $input        The CLI input interface (reading user input)
     *
     * @return bool The include-dev flag
     */
    private function readParamComposerIncludeDev($composerData, InputInterface $input)
    {
        if (null == $input->getOption('include-dev')) {
            if (array_key_exists('include-dev', $composerData)) {
                $input->setOption('include-dev', $composerData['include-dev']);
            } else {
                $input->setOption('include-dev', false);
            }
        }
        return $this->validateIncludeDev($input->getOption('include-dev'));
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
            $input->setOption('compression', 'GZip');
        } elseif ($input->hasParameterOption('-b') || $input->hasParameterOption('--bzip2')) {
            $input->setOption('compression', 'BZip2');
        } elseif ($input->hasParameterOption('-f') || $input->hasParameterOption('--no-compression')) {
            $input->setOption('compression', 'No');
        }
    }

    /**
     * Do nothing.
     * Normally prompt the user to add directory in the phar,
     * but this option is only read in CLI param and composer.json.
     *
     * @param InputInterface  $input   The CLI input interface (reading user input)
     * @param OutputInterface $output  The CLI output interface (display message)
     * @param string          $baseDir The path to the directory that contains the composer.json file
     *
     * @return array An empty array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) -- Do nothing function
     *///@codingStandardsIgnoreLine
    protected function askInclude(InputInterface $input, OutputInterface $output, $baseDir)
    {
        // Do nothing
        return array();
    }

    /**
     * Validate if the value are valid directories
     *
     * @param array $value List of path to directories
     *
     * @return array List of directory path to include
     */
    protected function validateInclude($value)
    {
        foreach ($value as $key => $dir) {
            if (!file_exists($dir) || !is_dir($dir)) {
                unset($value[$key]);
                $this->ioStyle->warning('Warning: the path "' . $dir . '" is not a valid directory. Path ignored.');
            }
        }
        return $value;
    }
}
