<?php

namespace MacFJA\PharBuilder\Utils\Config;

/**
 * Class Composer.
 *
 * Configuration reader of the path of the **composer.json** file
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class Composer extends BaseConfig
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
                    new Definition\Argument($this->input, 'composer')
                );
        }
        $this->chain
            ->addConfig(
                new Definition\FileChooser(
                    $this->input,
                    $this->output,
                    $this->fileChooser,
                    'Where is your application <option=bold>composer.json</option=bold> file? ',
                    './composer.json'
                )
            )
            ->addConfig(
                new Definition\Guess(
                    /**
                     * Try to find a `composer.json` file in the current directory
                     *
                     * @return string|null
                     */
                    function () {
                        $currentDirectory = getcwd();
                        if (file_exists($currentDirectory . DIRECTORY_SEPARATOR . 'composer.json')) {
                            return $currentDirectory . DIRECTORY_SEPARATOR . 'composer.json';
                        }
                        return null;
                    }
                )
            );

        $this->validator = new Validator\Chain();
        $this->validator
            ->addValidator(new Validator\Path(Validator\Path::EXIST))
            ->addValidator(new Validator\Path(Validator\Path::IS_FILE))
            ->addValidator(new Validator\Text(Validator\Text::END_WITH, 'composer.json'))
            ->setErrorCode(1)
            ->setErrorMessages(array(
                'The path provided is not a valid <option=bold>composer.json</option=bold> file,' . PHP_EOL .
                'or the current directory does not contain a <option=bold>composer.json</option=bold> file.',
                'Path: %s'
            ))
            ->setIoStyle($this->ioStyle);
    }
}
