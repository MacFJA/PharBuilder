<?php


namespace MacFJA\PharBuilder;

use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use MacFJA\Symfony\Console\Filechooser\FileFilter;
use Symfony\Component\Console\Application as Base;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

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
 *
 * @author MacFJA
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
        $app = $this;

        // Register the full interactive command
        $this->register('build')->setDescription('Full interactive Phar builder')->setCode(
            function (InputInterface $input, OutputInterface $output) use ($app) {
                // -- Initialise reusable variables

                $output->getFormatter()->setStyle('success', new OutputFormatterStyle('white', 'green'));

                // -- Ask for composer.json file (the base file of the project)
                $composerFile = $this->askComposer($input, $output);

                // -- Ask for the stub <=> the entry point of the application
                $stubFile = $app->askEntryPoint($input, $output, dirname($composerFile));

                // -- Ask for the compression
                $compression = $app->askCompression($input, $output);

                // -- Ask for the name of the phar file
                $name = $app->askName($input, $output);

                // -- Ask for the output folder
                $outputDir = $app->askOutputDir($input, $output, dirname($composerFile));

                // -- Build the Phar

                $output->writeln('');
                new PharBuilder($output, $composerFile, $outputDir, $name, $stubFile, $compression, array());
                $output->writeln('');
            }
        );


        $this->register('package')
            ->addArgument('composer', InputArgument::REQUIRED, 'The path to the composer.json')
            ->addOption('entry-point', 'e', InputOption::VALUE_REQUIRED, 'Your application start file')
            ->addOption(
                'compression',
                '',
                InputOption::VALUE_REQUIRED,
                'The compression of your Phar ' .
                '(possible values <option=bold>No</option=bold>, <option=bold>GZip</option=bold>, <option=bold>BZip2</option=bold>)'
            )
            ->addOption('no-compression', 'f', InputOption::VALUE_NONE, 'Do not compress the Phar')
            ->addOption('gzip', 'z', InputOption::VALUE_NONE, 'Set the compression of the Phar to GZip')
            ->addOption('bzip2', 'b', InputOption::VALUE_NONE, 'Set the compression of the Phar to BZip2')
            ->addOption('name', '', InputOption::VALUE_REQUIRED, 'The filename of the Phar archive')
            ->addOption('output-dir', 'o', InputOption::VALUE_REQUIRED, 'The output directory of the Phar archive')
            ->addOption('include', 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'List of directories to add in Phar')
            ->setHelp(
                'Create a Phar file of a composer project.' . PHP_EOL .
                'The command can get values from CLI argument, by reading composer file or by ask (in this order)' . PHP_EOL .
                'If an option is both defined in the composer file and in the CLI argument, the CLI argument will be used.' . PHP_EOL .
                'Example of a composer configuration:' . PHP_EOL . PHP_EOL .
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
            ->setDescription('Create a Phar file from a composer.json')
            ->setCode(
                function (InputInterface $input, OutputInterface $output) use ($app) {
                    $composerFile = $input->getArgument('composer');

                    if (!file_exists($composerFile) || !is_file($composerFile) || basename($composerFile) != 'composer.json') {
                        $app->renderException(
                            new \InvalidArgumentException('The path provided is not a valid <option=bold>composer.json</option=bold> file'),
                            $output
                        );
                        exit(1);
                    }
                    $output->getFormatter()->setStyle('success', new OutputFormatterStyle('white', 'green'));

                    $composerFile = realpath($composerFile);
                    $baseDir = dirname($composerFile);
                    chdir($baseDir);

                    /*
                     * Read the composer.json file.
                     * All information we need is store in it.
                     */
                    $parsed = json_decode(file_get_contents($composerFile), true);
                    // check if our info is here
                    if (!isset($parsed['extra'])) {
                        $parsed['extra'] = array('phar-builder' => array());
                    } elseif (!isset($parsed['extra']['phar-builder'])) {
                        $parsed['extra']['phar-builder'] = array();
                    }
                    $extraData = $parsed['extra']['phar-builder'];

                    $this->readSpecialParams($input);

                    $stubFile = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'entry-point');
                    $compression = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'compression');
                    $name = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'name');
                    $outputDir = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'output-dir');
                    $includes = $this->readParamComposerAsk($extraData, $input, $output, $baseDir, 'include');

                    $output->writeln('');
                    new PharBuilder($output, $composerFile, $outputDir, $name, $stubFile, $compression, $includes);
                    $output->writeln('');
                }
            );

        /*
         * Set the build command the default command.
         * So without argument, instead of the help command, the build command will be launch
         */
        $this->setDefaultCommand('build');
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
            if (isset($composerData[$dataName])) {
                $input->setOption($dataName, $composerData[$dataName]);
            } else {
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
     * Read and transform special option
     *
     * @param InputInterface $input The CLI input interface (reading user input)
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
     * Get the function name for ask or validate action for a specific data
     *
     * @param string $type     The function type to get (ask or validate)
     * @param string $dataName The name of the data (hyphen word separated)
     *
     * @return string The formatted function name
     */
    private function getFunctionName($type, $dataName)
    {
        $result = str_replace('-', ' ', $dataName);
        $result = ucwords($result);
        $result = str_replace(' ', '', $result);
        return strtolower($type) . $result;
    }

    /**
     * Prompt the user to select the project's composer.json
     *
     * @param InputInterface  $input  The CLI input interface (reading user input)
     * @param OutputInterface $output
     *
     * @return string The composer.json real path
     */
    protected function askComposer(InputInterface $input, OutputInterface $output)
    {
        /** @var FilechooserHelper $filechooser */
        $filechooser = $this->getHelperSet()->get('filechooser');

        $composerFilter = new FileFilter('Where is your application <option=bold>composer.json</option=bold> file? ', './composer.json');
        $composerFile = $filechooser->ask($input, $output, $composerFilter);

        $this->validateComposer($composerFile, $output);

        return realpath($composerFile);
    }

    /**
     * Validate if the value is a valid composer.json file path.
     *
     * @param string          $value  A path to a composer file
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The validated path to composer.json
     */
    protected function validateComposer($value, OutputInterface $output)
    {
        if (!file_exists($value) || !is_file($value) || basename(
                $value
            ) != 'composer.json'
        ) {
            $this->renderException(
                new \InvalidArgumentException('The path provided is not a valid <option=bold>composer.json</option=bold> file' . PHP_EOL . '  Path: ' . $value),
                $output
            );
            exit(1);
        }
        return $value;
    }

    /**
     * Prompt to the user the entry (stub) file. It's the file that launch the application
     *
     * @param InputInterface  $input   The CLI input interface (reading user input)
     * @param OutputInterface $output  The CLI output interface (display message)
     * @param string          $baseDir The path to the directory that contains the composer.json file
     *
     * @return string The path to the entry point of the application
     */
    protected function askEntryPoint(InputInterface $input, OutputInterface $output, $baseDir)
    {
        /** @var FilechooserHelper $filechooser */
        $filechooser = $this->getHelperSet()->get('filechooser');

        $stubFilter = new FileFilter('Where is your application start file? ', $baseDir . DIRECTORY_SEPARATOR . 'index.php');
        $stubFile = $filechooser->ask($input, $output, $stubFilter);

        $this->validateEntryPoint($stubFile, $output);

        return $stubFile;
    }

    /**
     * Validate if the value is a valid file path.
     *
     * @param string          $value  A path to a file
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The validated path to the application entry point
     */
    protected function validateEntryPoint($value, OutputInterface $output)
    {
        if (!file_exists($value) || !is_file($value)) {
            $this->renderException(
                new \InvalidArgumentException('The path provided for the entry point is not a valid file' . PHP_EOL . '  Path: ' . $value),
                $output
            );
            exit(2);
        }
        return $value;
    }

    /**
     * Prompt to the user the compression option (default to None)
     *
     * @param InputInterface  $input  The CLI input interface (reading user input)
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The compression of the Phar
     */
    protected function askCompression(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        $compressChoice = new ChoiceQuestion('Do you want to compress the Phar? [<fg=blue>0</fg=blue>]', array(
            0 => 'No',
            1 => 'Yes in GZip',
            2 => 'Yes in BZip2'
        ), 0);
        switch ($questionHelper->ask($input, $output, $compressChoice)) {
            case 'No':
                return 'No';
            case 'Yes in GZip':
                return 'Gzip';
            case 'Yes in BZip2':
                return 'BZip2';
            default:
                return 'No';
        }
    }

    /**
     * Validate if the value is a valid compression.
     * Do nothing because compression is valid in \MacFJA\PharBuilder\PharBuilder class
     *
     * @param string          $value  A compression format
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The (not really) validated compression
     */
    protected function validateCompression($value, OutputInterface $output)
    {
        // Do nothing
        return $value;
    }

    /**
     * Prompt to the user the file name of the Phar (default to "app.phar")
     *
     * @param InputInterface  $input  The CLI input interface (reading user input)
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The name of the Phar file
     */
    protected function askName(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');

        $nameQuestion = new Question('What is the name of the phar? [<fg=blue>app.phar</fg=blue>] ', 'app.phar');

        return $questionHelper->ask($input, $output, $nameQuestion);
    }

    /**
     * Validate if the value is a valid filename
     *
     * @param string          $value  A filename
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string A validated filename
     */
    protected function validateName($value, OutputInterface $output)
    {
        if (strpos($value, DIRECTORY_SEPARATOR) !== false) {
            $this->renderException(
                new \InvalidArgumentException('The name for the Phar is not a valid filename' . PHP_EOL . '  Name: ' . $value),
                $output
            );
            exit(4);
        }
        return $value;
    }

    /**
     * Prompt to the user the path where to output the Phar archive
     *
     * @param InputInterface  $input   The CLI input interface (reading user input)
     * @param OutputInterface $output  The CLI output interface (display message)
     * @param string          $baseDir The path to the directory that contains the composer.json file
     *
     * @return string The path to the output directory
     */
    protected function askOutputDir(InputInterface $input, OutputInterface $output, $baseDir)
    {
        /** @var FilechooserHelper $filechooser */
        $filechooser = $this->getHelperSet()->get('filechooser');

        $outputFilter = new FileFilter('Where do you want to save your phar application? ', dirname(
            $baseDir
        ));
        $outputFilter->directories();
        $outputDir = $filechooser->ask($input, $output, $outputFilter);

        $this->validateOutputDir($outputDir, $output);

        return $outputDir;
    }

    /**
     * Validate if the value is a valid output directory
     *
     * @param string          $value  A path to a directory
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The validated output directory path
     */
    protected function validateOutputDir($value, OutputInterface $output)
    {
        if (!file_exists($value) || !is_dir($value)) {
            $this->renderException(
                new \InvalidArgumentException('The path provided for the output directory is not a valid directory' . PHP_EOL . '  Path: ' . $value),
                $output
            );
            exit(3);
        }
        return $value;
    }

    /**
     * Do nothing.
     * Normally prompt the user to add directory in the phar, but this option is only read in CLI param and composer.json.
     *
     * @param InputInterface  $input   The CLI input interface (reading user input)
     * @param OutputInterface $output  The CLI output interface (display message)
     * @param string          $baseDir The path to the directory that contains the composer.json file
     *
     * @return array An empty array
     */
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

    /**
     * Format code to be displayed in CLI help
     *
     * @param string $code The text to format
     *
     * @return string A formatted help text
     */
    private function codeHelpParagraph($code)
    {
        $result = '  │<comment>  ' . str_replace(
                PHP_EOL,
                '</comment>' . PHP_EOL . '  │<comment>  ',
                $code
            ) . '</comment>';

        return '  ┌' . PHP_EOL . $result . PHP_EOL . '  └';
    }
}