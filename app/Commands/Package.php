<?php


namespace MacFJA\PharBuilder\Commands;

use MacFJA\PharBuilder\PharBuilder;
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
        $this->setName('package')
            ->addArgument('composer', InputArgument::REQUIRED, 'The path to the composer.json')
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
            ->setHelp(
                file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'package-help.txt') .
                $this->codeHelpParagraph(
                    <<< CODE
<info>... The content of your composer.json file</info>
"extra": {
    "phar-builder": {
        "compression": "GZip",
        "name": "application.phar",
        "output-dir": "../",
        "entry-point": "./index.php",
        "include": ["bin","js","css"]
    }
}
CODE
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerFile = $input->getArgument('composer');

        $this->validateComposer($composerFile, $output);

        $composerFile = realpath($composerFile);
        $baseDir      = dirname($composerFile);
        chdir($baseDir);

        /*
         * Read the composer.json file.
         * All information we need is store in it.
         */
        $parsed = json_decode(file_get_contents($composerFile), true);
        // check if our info is here
        if (!array_key_exists('extra', $parsed)) {
            $parsed['extra'] = array('phar-builder' => array());
        } elseif (!array_key_exists('phar-builder', $parsed['extra'])) {
            $parsed['extra']['phar-builder'] = array();
        }
        $extraData = $parsed['extra']['phar-builder'];

        $this->readSpecialParams($input);

        $stubFile    = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'entry-point');
        $compression = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'compression');
        $name        = $this->readParamComposerAskName($extraData, $input, $output);
        $outputDir   = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'output-dir');
        $includes    = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'include');

        $output->writeln('');
        new PharBuilder($output, $composerFile, $outputDir, $name, $stubFile, $compression, $includes);
        $output->writeln('');
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
        if ($input->getOption($dataName) == null) {
            if (array_key_exists($dataName, $composerData)) {
                $input->setOption($dataName, $composerData[$dataName]);
            } else {
                if (!$input->isInteractive()) {
                    $this->throwErrorForNoInteractiveMode($output);
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
            array($input->getOption($dataName), $output)
        );

        return $data;
    }

    /**
     * Read the CLI argument for the phar name,
     * if not found read the Composer.json file,
     * if not provided in composer.json, ask the user
     *
     * @param array           $composerData The composer.json extra data content
     * @param InputInterface  $input        The CLI input interface (reading user input)
     * @param OutputInterface $output       The CLI output interface (display message)
     *
     * @return mixed The name of the phar
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function readParamComposerAskName($composerData, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('name') == null) {
            if (array_key_exists('name', $composerData)) {
                $input->setOption('name', $composerData['name']);
            } else {
                if (!$input->isInteractive()) {
                    $this->throwErrorForNoInteractiveMode($output);
                }
                $input->setOption('name', $this->askPharName($input, $output));
            }
        }
        return $this->validatePharName($input->getOption('name'), $output);
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
     * @param array           $value  List of path to directories
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return array List of directory path to include
     */
    protected function validateInclude($value, OutputInterface $output)
    {
        foreach ($value as $key => $dir) {
            if (!file_exists($dir) || !is_dir($dir)) {
                unset($value[$key]);
                $output->writeln(
                    '<error>Warning: the path "' . $dir . '" is not a valid directory. Path ignored.</error>'
                );
            }
        }
        return $value;
    }
}
