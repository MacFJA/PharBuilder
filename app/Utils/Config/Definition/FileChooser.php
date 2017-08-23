<?php

namespace MacFJA\PharBuilder\Utils\Config\Definition;

use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use MacFJA\Symfony\Console\Filechooser\FileFilter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FileChooser.
 * Prompt to the use a CLI file selector
 *
 * @package MacFJA\PharBuilder\Utils\Config\Definition
 * @author  MacFJA
 * @license MIT
 */
class FileChooser implements Definition
{
    /**
     * The CLI input interface (reading user input)
     *
     * @var InputInterface
     */
    protected $input;
    /**
     * The input helper to select a file
     *
     * @var FilechooserHelper
     */
    protected $fileChooser;
    /**
     * The CLI output interface (display message)
     *
     * @var OutputInterface
     */
    private $output;
    /**
     * The question text to display
     *
     * @var string
     */
    private $question;
    /**
     * The default path
     *
     * @var string|null
     */
    private $default;
    /**
     * Indicate if the selection must be a directory
     *
     * @var bool
     */
    private $directories;

    /**
     * Console constructor.
     *
     * @param InputInterface    $input       The CLI input interface (reading user input)
     * @param OutputInterface   $output      The CLI output interface (display message)
     * @param FilechooserHelper $fileChooser The input helper to select a file
     * @param string            $question    The question text to display
     * @param string|null       $default     The default path
     * @param bool|false        $directories Indicate if the selection must be a directory
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        FilechooserHelper $fileChooser,
        $question,
        $default = null,
        $directories = false
    ) {
    

        $this->input       = $input;
        $this->output      = $output;
        $this->fileChooser = $fileChooser;
        $this->question    = $question;
        $this->default     = $default;
        $this->directories = $directories;
    }

    /**
     * Does this configuration reader have the value
     *
     * @return bool
     */
    public function has()
    {
        if (!$this->input->isInteractive()) {
            return false;
        }

        return true;
    }

    /**
     * Get the configuration value
     *
     * @return mixed|null
     */
    public function get()
    {
        $outputFilter = new FileFilter($this->question, $this->default);
        if ($this->directories) {
            $outputFilter->directories();
        }
        $outputDir = $this->fileChooser->ask($this->input, $this->output, $outputFilter);

        return $outputDir;
    }
}
