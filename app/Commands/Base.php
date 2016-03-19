<?php


namespace MacFJA\PharBuilder\Commands;


use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use MacFJA\Symfony\Console\Filechooser\FileFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class Base.
 * Contains all shared function for the getting all phar information
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\PharBuilder\Commands
 */
abstract class Base extends Command
{
    /**
     * Get the function name for ask or validate action for a specific data
     *
     * @param string $type     The function type to get (ask or validate)
     * @param string $dataName The name of the data (hyphen word separated)
     *
     * @return string The formatted function name
     */
    protected function getFunctionName($type, $dataName)
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
        $filechooser = $this->getApplication()->getHelperSet()->get('filechooser');

        $composerFilter = new FileFilter('Where is your application <option=bold>composer.json</option=bold> file? ', './composer.json');
        $composerFile = $filechooser->ask($input, $output, $composerFilter);

        $this->validateComposer($composerFile, $output);

        return realpath($composerFile);
    }

    /**
     * Validate if the value is a valid composer.json file path.
     * Exit code: `1`
     *
     * @param string          $value  A path to a composer file
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The validated path to composer.json
     */
    protected function validateComposer($value, OutputInterface $output)
    {
        if (!file_exists($value) || !is_file($value) || 'composer.json' !== basename($value)) {
            $this->getApplication()->renderException(
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
        $filechooser = $this->getApplication()->getHelperSet()->get('filechooser');

        $stubFilter = new FileFilter('Where is your application start file? ', $baseDir . DIRECTORY_SEPARATOR . 'index.php');
        $stubFile = $filechooser->ask($input, $output, $stubFilter);

        $this->validateEntryPoint($stubFile, $output);

        return $stubFile;
    }

    /**
     * Validate if the value is a valid file path.
     * Exit code: `2`
     *
     * @param string          $value  A path to a file
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The validated path to the application entry point
     */
    protected function validateEntryPoint($value, OutputInterface $output)
    {
        if (!file_exists($value) || !is_file($value)) {
            $this->getApplication()->renderException(
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
        $questionHelper = $this->getApplication()->getHelperSet()->get('question');

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
        $questionHelper = $this->getApplication()->getHelperSet()->get('question');

        $nameQuestion = new Question('What is the name of the phar? [<fg=blue>app.phar</fg=blue>] ', 'app.phar');

        return $questionHelper->ask($input, $output, $nameQuestion);
    }

    /**
     * Validate if the value is a valid filename.
     * Exit code: `4`
     *
     * @param string          $value  A filename
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string A validated filename
     */
    protected function validateName($value, OutputInterface $output)
    {
        if (strpos($value, DIRECTORY_SEPARATOR) !== false) {
            $this->getApplication()->renderException(
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
        $filechooser = $this->getApplication()->getHelperSet()->get('filechooser');

        $outputFilter = new FileFilter('Where do you want to save your phar application? ', dirname(
            $baseDir
        ));
        $outputFilter->directories();
        $outputDir = $filechooser->ask($input, $output, $outputFilter);

        $this->validateOutputDir($outputDir, $output);

        return $outputDir;
    }

    /**
     * Validate if the value is a valid output directory.
     * Exit code: `3`
     *
     * @param string          $value  A path to a directory
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The validated output directory path
     */
    protected function validateOutputDir($value, OutputInterface $output)
    {
        if (is_file($value)) {
            $this->getApplication()->renderException(
                new \InvalidArgumentException('The path provided for the output directory is not a valid directory' . PHP_EOL . '  Path: ' . $value),
                $output
            );
            exit(3);
        }
        if (!file_exists($value) && !mkdir($value, 0755, true)) {
            $this->getApplication()->renderException(
                new \InvalidArgumentException('Unable to create the output directory.' . PHP_EOL . '  Path: ' . $value),
                $output
            );
            exit(3);
        }
        if (!is_writable($value)) {
            $this->getApplication()->renderException(
                new \InvalidArgumentException('The output directory is not writable.' . PHP_EOL . '  Path: ' . $value),
                $output
            );
            exit(3);
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
    protected function codeHelpParagraph($code)
    {
        $result = '  │<comment>  ' . str_replace(
                PHP_EOL,
                '</comment>' . PHP_EOL . '  │<comment>  ',
                $code
            ) . '</comment>';

        return '  ┌' . PHP_EOL . $result . PHP_EOL . '  └';
    }

    /**
     * Display an error that indicate that the application is in a no interactive mode and require an input.
     * Exit code: `6`
     *
     * @param OutputInterface $output The CLI output interface (display message)
     */
    protected function throwErrorForNoInteractiveMode(OutputInterface $output)
    {
        $this->getApplication()->renderException(
            new \InvalidArgumentException('The terminal set the application in a no-interactive mode. therefor this command cannot be used as its require input'),
            $output
        );
        exit(6);
    }
}