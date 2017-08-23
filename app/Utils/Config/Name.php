<?php

namespace MacFJA\PharBuilder\Utils\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Name.
 *
 * Configuration for the file name of the Phar (default to "app.phar")
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class Name extends BaseConfig
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
                    new Definition\Param($this->input, 'name')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'name')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\SymfonyStyle(
                    $this->input,
                    $this->ioStyle,
                    /**
                     * Ask for the name of the phar
                     *
                     * @param SymfonyStyle $ioStyle The Input/Output helper
                     *
                     * @return string
                     */
                    function (SymfonyStyle $ioStyle) {
                        return $ioStyle->ask('What is the name of the phar?', 'app.phar');
                    }
                )
            )
            ->addConfig(
                new Definition\Guess(
                    /**
                     * Read the composer.json file to guess the project name
                     *
                     * @param \MacFJA\PharBuilder\Utils\Composer $composerReader The composer reader
                     *
                     * @return string|null
                     */
                    function (\MacFJA\PharBuilder\Utils\Composer $composerReader) {
                        $packageName = $composerReader->getValue('name');
                        if ($packageName === null) {
                            return null;
                        }

                        list(, $name) = explode('/', $packageName);
                        return $name . '.phar';
                    },
                    array($this->composerReader)
                )
            )
            ->addConfig(
                new Definition\FixValue('app.phar')
            );

        $this->validator = new Validator\Callback(
            /**
             * Validate a phar name
             *
             * @param string $value The name to validate
             *
             * @return bool
             */
            function ($value) {
                return strpos($value, DIRECTORY_SEPARATOR) === false;
            }
        );
        $this->validator
            ->setErrorCode(4)
            ->setErrorMessages(array('The name for the Phar is not a valid filename', 'Name: %s'))
            ->setIoStyle($this->ioStyle);
    }
}
