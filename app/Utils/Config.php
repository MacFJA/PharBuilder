<?php

namespace MacFJA\PharBuilder\Utils;

use MacFJA\PharBuilder\Utils\Config\BaseConfig;
use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Config.
 * The configuration reader factory
 *
 * @package MacFJA\PharBuilder\Utils
 * @author  MacFJA
 * @license MIT
 */
class Config
{
    /**
     * Indicate if the command parameter/option/argument must be read
     *
     * @var bool
     */
    protected $readParam = false;
    /**
     * Indicate if the composer extra section must be read
     *
     * @var bool
     */
    protected $readExtra = false;
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
    protected $composerExtra = array();
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
     * @var string|null
     */
    protected $composerDir;

    /**
     * Set if the command parameter/option/argument must be read
     *
     * @param boolean $readParam The flag
     *
     * @return Config
     */
    public function setReadParam($readParam)
    {
        $this->readParam = $readParam;
        return $this;
    }

    /**
     * Set if the composer extra section must be read
     *
     * @param boolean $readExtra The flag
     *
     * @return Config
     */
    public function setReadExtra($readExtra)
    {
        $this->readExtra = $readExtra;
        return $this;
    }

    /**
     * Set the content of the composer.json extra section
     *
     * @param array $composerExtra The extra section content
     *
     * @return Config
     */
    public function setComposerExtra($composerExtra)
    {
        $this->composerExtra = $composerExtra;
        $this->setReadExtra(true);
        return $this;
    }

    /**
     * Set The Utility class to use for reading composer.json file
     *
     * @param Composer $composerReader The Utility class to read composer.json file
     *
     * @return Config
     */
    public function setComposerReader($composerReader)
    {
        $this->composerReader = $composerReader;
        return $this;
    }

    /**
     * Set the path to the directory that contain the composer.json
     *
     * @param string $composerDir The path to the directory that contain the composer.json
     *
     * @return Config
     */
    public function setComposerDir($composerDir)
    {
        $this->composerDir = $composerDir;
        return $this;
    }


    /**
     * Config constructor.
     *
     * @param InputInterface    $input       The CLI input interface (reading user input)
     * @param OutputInterface   $output      The CLI output interface (display message)
     * @param FilechooserHelper $fileChooser The input helper to select a file
     * @param SymfonyStyle      $ioStyle     The Input/Output helper
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        FilechooserHelper $fileChooser,
        SymfonyStyle $ioStyle
    ) {
    

        $this->input       = $input;
        $this->output      = $output;
        $this->fileChooser = $fileChooser;
        $this->ioStyle     = $ioStyle;
    }

    /**
     * List of all configuration reader
     *
     * @var array
     */
    protected $configList = array(
        'composer' => Config\Composer::class,
        'compression' => Config\Compression::class,
        'entry-point' => Config\EntryPoint::class,
        'include-dev' => Config\IncludeDev::class,
        'includes' => Config\Includes::class,
        'name' => Config\Name::class,
        'output-dir' => Config\OutputDir::class,
        'shebang' => Config\Shebang::class,
    );

    /**
     * List of instantiate configuration reader
     *
     * @var BaseConfig[]
     */
    protected $configs = array();

    /**
     * Get the configuration value
     *
     * @param string $configName Tne configuration name
     *
     * @return mixed|null
     */
    public function getValue($configName)
    {
        if (!array_key_exists($configName, $this->configList)) {
            return null;
        }

        if (!array_key_exists($configName, $this->configs)) {

            /**
             * The configuration reader
             *
             * @var BaseConfig $class
             */
            $class = new $this->configList[$configName](
                $this->readParam,
                $this->readExtra,
                $this->input,
                $this->ioStyle,
                $this->output,
                $this->fileChooser,
                $this->composerExtra,
                $this->composerReader,
                $this->composerDir
            );

            $this->configs[$configName] = $class;
        }
        return $this->configs[$configName]->get();
    }
}
