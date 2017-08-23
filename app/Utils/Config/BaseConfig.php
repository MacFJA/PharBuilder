<?php

namespace MacFJA\PharBuilder\Utils\Config;

use MacFJA\PharBuilder\Utils\Composer;
use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BaseConfig.
 *
 * Act as a definition of what are capable a configuration and what they must implement.
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
abstract class BaseConfig
{
    /**
     * Indicate if the command parameter/option/argument must be read
     *
     * @var bool
     */
    protected $readParam;
    /**
     * Indicate if the composer extra section must be read
     *
     * @var bool
     */
    protected $readExtra;
    /**
     * The configuration chain to use
     *
     * @var Definition\Chain
     */
    protected $chain;
    /**
     * The CLI input interface (reading user input)
     *
     * @var InputInterface
     */
    protected $input;
    /**
     * The content of the composer.json extra section
     *
     * @var array
     */
    protected $composerExtra;
    /**
     * The CLI output interface (display message)
     *
     * @var OutputInterface
     */
    protected $output;
    /**
     * The Utility class to read composer.json file
     *
     * @var Composer|null
     */
    protected $composerReader;
    /**
     * The input helper to select a file
     *
     * @var FilechooserHelper
     */
    protected $fileChooser;
    /**
     * The Input/Output helper
     *
     * @var SymfonyStyle
     */
    protected $ioStyle;
    /**
     * The path to the directory that contain the composer.json
     *
     * @var string
     */
    protected $composerDir;
    /**
     * The validator to use
     *
     * @var Validator\BaseValidator
     */
    protected $validator;
    /**
     * The last result of the configuration reading
     *
     * @var null|mixed
     */
    protected $lastResult;

    /**
     * BaseConfig constructor.
     *
     * @param bool              $readParam      Indicate if the command parameter/option/argument must be read
     * @param bool              $readExtra      Indicate if the composer extra section must be read
     * @param InputInterface    $input          The CLI input interface (reading user input)
     * @param SymfonyStyle      $ioStyle        The Input/Output helper
     * @param OutputInterface   $output         The Utility class to read composer.json file
     * @param FilechooserHelper $fileChooser    The input helper to select a file
     * @param array             $composerExtra  The content of the composer.json extra section
     * @param Composer          $composerReader The Utility class to read composer.json file
     * @param string            $composerDir    The path to the directory that contain the composer.json
     */
    public function __construct(
        $readParam,
        $readExtra,
        InputInterface $input,
        SymfonyStyle $ioStyle,
        OutputInterface $output,
        FilechooserHelper $fileChooser,
        array $composerExtra = array(),
        Composer $composerReader = null,
        $composerDir = ''
    ) {
    

        $this->readParam      = $readParam;
        $this->readExtra      = $readExtra;
        $this->input          = $input;
        $this->composerExtra  = $composerExtra;
        $this->output         = $output;
        $this->composerReader = $composerReader;
        $this->fileChooser    = $fileChooser;
        $this->ioStyle        = $ioStyle;
        $this->composerDir    = $composerDir;

        $this->chain     = new Definition\Chain();
        $this->validator = new Validator\Always();

        $this->init();
    }

    /**
     * Get the value
     *
     * @return mixed|null
     */
    public function get()
    {
        if ($this->lastResult === null) {
            $this->lastResult = $this->chain->get();

            if (!$this->validator->validate($this->lastResult)) {
                $this->lastResult = null;
            }
        }
        return $this->lastResult;
    }

    /**
     * The the default error code and messages
     *
     * @return void
     */
    protected function setBaseError()
    {
        $this->validator
            ->setErrorCode(7)
            ->setErrorMessages(array('Unexpected value.', 'Value: %s'))
            ->setIoStyle($this->ioStyle);
    }

    /**
     * Initialize the configuration reader
     *
     * @return void
     */
    abstract protected function init();
}
