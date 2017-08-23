<?php

namespace MacFJA\PharBuilder\Utils\Config;

/**
 * Class OutputDir.
 *
 * Configuration for the path where to generate the Phar archive
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class OutputDir extends BaseConfig
{
    /**
     * Initialize the configuration reader
     *
     * @return void
     */
    protected function init()
    {
        if ($this->readParam) {
            $this->chain
                ->addConfig(
                    new Definition\Param($this->input, 'output-dir')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'output-dir')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\FileChooser(
                    $this->input,
                    $this->output,
                    $this->fileChooser,
                    'Where do you want to save your phar application? ',
                    dirname($this->composerDir),
                    true
                )
            )
            ->addConfig(
                new Definition\FixValue(dirname($this->composerDir))
            );

        $this->validator = new Validator\Chain();
        $this->validator
            ->addValidator(
                Validator\Path::newIsDir()
                    ->setIoStyle($this->ioStyle)
                    ->setErrorMessages(
                        array('The path provided for the output directory is not a valid directory', 'Path: %s')
                    )
            )
            ->addValidator(
                Validator\Path::newHierarchyExist()
                    ->setIoStyle($this->ioStyle)
                    ->setErrorMessages(
                        array('Unable to create the output directory.', 'Path: %s')
                    )
            )
            ->addValidator(
                Validator\Path::newWritable()
                    ->setIoStyle($this->ioStyle)
                    ->setErrorMessages(
                        array('The output directory is not writable.', 'Path: %s')
                    )
            )
            ->setErrorCode(3);
    }
}
