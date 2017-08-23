<?php

namespace MacFJA\PharBuilder\Utils\Config;

/**
 * Class EntryPoint.
 *
 * Configuration for the entry (stub) file. It's the file that launch the application
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class EntryPoint extends BaseConfig
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
                    new Definition\Param($this->input, 'entry-point')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'entry-point')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\FileChooser(
                    $this->input,
                    $this->output,
                    $this->fileChooser,
                    'Where is your application start file? ',
                    $this->composerDir . DIRECTORY_SEPARATOR . 'index.php'
                )
            )
            ->addConfig(
                new Definition\Guess(
                    /**
                     * Search for common entry-point
                     *
                     * @param string $baseDir The directory to search in
                     *
                     * @return string|null
                     */
                    function ($baseDir) {
                        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);

                        /*
                         * Search for:
                         *  - index.php
                         *  - cli.php
                         *  - web/app.php [symfony]
                         *  - web/app-dev.php [symfony]
                         *  - www/index.php [nette]
                         *  - public/index.php [laravel|phalcon|fuel|zf3|zf2]
                         *  - web/index.php [yii2]
                         */
                        foreach (array(
                                     'index.php',
                                     'cli.php',
                                     'web' . DIRECTORY_SEPARATOR . 'app.php',
                                     'web' . DIRECTORY_SEPARATOR . 'app_dev.php',
                                     'www' . DIRECTORY_SEPARATOR . 'index.php',
                                     'public' . DIRECTORY_SEPARATOR . 'index.php',
                                     'web' . DIRECTORY_SEPARATOR . 'index.php'
                                 ) as $entry) {
                            if (file_exists($baseDir . DIRECTORY_SEPARATOR . $entry)) {
                                return $entry;
                            }
                        }

                        return null;
                    },
                    array($this->composerDir)
                )
            )
            ->addConfig(
                new Definition\FixValue('index.php')
            );

        $this->validator = new Validator\Chain();
        $this->validator
            ->addValidator(new Validator\Path(Validator\Path::EXIST))
            ->addValidator(new Validator\Path(Validator\Path::IS_FILE))
            ->setErrorCode(2)
            ->setErrorMessages(array('The path provided for the entry point is not a valid file', 'Path: %s'))
            ->setIoStyle($this->ioStyle);
    }
}
