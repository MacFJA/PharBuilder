<?php


namespace MacFJA\PharBuilder\Commands;

use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use MacFJA\Symfony\Console\Filechooser\FileFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
    protected $ioStyle;

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
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return string The composer.json real path
     *
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function askComposer(InputInterface $input, OutputInterface $output)
    {
        /**
         * The file selector helper
         *
         * @var FilechooserHelper $filechooser
         */
        $filechooser = $this->getApplication()->getHelperSet()->get('filechooser');

        $composerFilter = new FileFilter(
            'Where is your application <option=bold>composer.json</option=bold> file? ',
            './composer.json'
        );
        $composerFile   = $filechooser->ask($input, $output, $composerFilter);

        $this->validateComposer($composerFile);

        return realpath($composerFile);
    }

    /**
     * Validate if the value is a valid composer.json file path.
     * Exit code: `1`
     *
     * @param string $value A path to a composer file
     *
     * @return string The validated path to composer.json
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    protected function validateComposer($value)
    {
        if (!file_exists($value) || !is_file($value) || 'composer.json' !== basename($value)) {
            $this->ioStyle->error(array(
                'The path provided is not a valid <option=bold>composer.json</option=bold> file,' . PHP_EOL .
                'or the current directory does not contain a <option=bold>composer.json</option=bold> file.',
                'Path: ' . $value
            ));
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
     *
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function askEntryPoint(InputInterface $input, OutputInterface $output, $baseDir)
    {
        /**
         * The file selector helper
         *
         * @var FilechooserHelper $filechooser
         */
        $filechooser = $this->getApplication()->getHelperSet()->get('filechooser');

        $stubFilter = new FileFilter(
            'Where is your application start file? ',
            $baseDir . DIRECTORY_SEPARATOR . 'index.php'
        );
        $stubFile   = $filechooser->ask($input, $output, $stubFilter);

        $this->validateEntryPoint($stubFile);

        return $stubFile;
    }

    /**
     * Validate if the value is a valid file path.
     * Exit code: `2`
     *
     * @param string $value A path to a file
     *
     * @return string The validated path to the application entry point
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    protected function validateEntryPoint($value)
    {
        if (!file_exists($value) || !is_file($value)) {
            $this->ioStyle->error(
                array('The path provided for the entry point is not a valid file', 'Path: ' . $value)
            );
            exit(2);
        }
        return $value;
    }

    /**
     * Prompt to the user the compression option (default to None)
     *
     * @return string The compression of the Phar
     */
    protected function askCompression()
    {
        $choice = $this->ioStyle->choice('Do you want to compress the Phar?', array(
            'No',
            'Yes in GZip',
            'Yes in BZip2'
        ), 'No');

        switch ($choice) {
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
     * @param string $value A compression format
     *
     * @return string The (not really) validated compression
     */
    protected function validateCompression($value)
    {
        // Do nothing
        return $value;
    }

    /**
     * Prompt to the user the file name of the Phar (default to "app.phar")
     *
     * @return string The name of the Phar file
     */
    protected function askPharName()
    {
        return $this->ioStyle->ask('What is the name of the phar?', 'app.phar');
    }

    /**
     * Validate if the value is a valid filename.
     * Exit code: `4`
     *
     * @param string $value A filename
     *
     * @return string A validated filename
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    protected function validatePharName($value)
    {
        if (strpos($value, DIRECTORY_SEPARATOR) !== false) {
            $this->ioStyle->error(array(
                'The name for the Phar is not a valid filename',
                'Name: ' . $value
            ));
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
     *
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Exception
     */
    protected function askOutputDir(InputInterface $input, OutputInterface $output, $baseDir)
    {
        /**
         * The file selector helper
         *
         * @var FilechooserHelper $filechooser
         */
        $filechooser = $this->getApplication()->getHelperSet()->get('filechooser');

        $outputFilter = new FileFilter('Where do you want to save your phar application? ', dirname(
            $baseDir
        ));
        $outputFilter->directories();
        $outputDir = $filechooser->ask($input, $output, $outputFilter);

        $this->validateOutputDir($outputDir);

        return $outputDir;
    }

    /**
     * Validate if the value is a valid output directory.
     * Exit code: `3`
     *
     * @param string $value A path to a directory
     *
     * @return string The validated output directory path
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    protected function validateOutputDir($value)
    {
        if (is_file($value)) {
            $this->ioStyle->error(array(
                'The path provided for the output directory is not a valid directory',
                'Path: ' . $value
            ));
            exit(3);
        }
        if (!file_exists($value) && !mkdir($value, 0755, true)) {
            $this->ioStyle->error(array(
                'Unable to create the output directory.',
                'Path: ' . $value
            ));
            exit(3);
        }
        if (!is_writable($value)) {
            $this->ioStyle->error(array(
                'The output directory is not writable.',
                'Path: ' . $value
            ));
            exit(3);
        }
        return $value;
    }

    /**
     * Prompt to the user the compression option (default to None)
     *
     * @return string The compression of the Phar
     */
    protected function askIncludeDev()
    {
        return $this->ioStyle->confirm('Do you want to include dev?', false);
    }

    /**
     * Validate if the value is a valid flag for including dev
     * Do nothing because a flag as only 2 possible value which are, by definition, valid
     *
     * @param bool $value The flag about including dev code and packages
     *
     * @return bool The (not really) validated flag
     */
    protected function validateIncludeDev($value)
    {
        // Do nothing
        return $value;
    }

    /**
     * Prompt to the user the skip shebang flag.
     *
     * @return bool The skip shebang flag
     */
    protected function askSkipShebang()
    {
        return $this->ioStyle->confirm('Do you want to skip the shebang?', false);
    }

    /**
     * Validates the skip shebang flag by casting it bool.
     *
     * @param bool $value The skip shebang flag
     *
     * @return bool skip shebang flag
     */
    protected function validateSkipShebang($value)
    {
        return (bool) $value;
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

    /**
     * Display an error that indicate that the application is in a no interactive mode and require an input.
     * Exit code: `6`
     *
     * @param string|null $missedOption
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    protected function throwErrorForNoInteractiveMode($missedOption = null)
    {
        $message = 'The terminal set the application in a no-interactive mode.';
        if ($missedOption) {
            $message .= ' Disable no-interactive mode or describe "' . $missedOption .'" ' .
                'in composer.json (ex. https://github.com/MacFJA/PharBuilder/blob/master/docs/ComposerExtra.md)';
        }
        $this->ioStyle->error($message);
        exit(6);
    }
}
