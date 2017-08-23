<?php

namespace MacFJA\PharBuilder\Utils\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Shebang
 *
 * Configuration reader to know if we add the _shebang_ in the top of teh PHAR
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class Shebang extends BaseConfig
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
                    new Definition\Param($this->input, 'skip-shebang')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'skip-shebang')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\SymfonyStyle(
                    $this->input,
                    $this->ioStyle,
                    /**
                     * Ask if we want to skip the shebang
                     *
                     * @param SymfonyStyle $ioStyle The Input/Output helper
                     *
                     * @return bool
                     */
                    function (SymfonyStyle $ioStyle) {
                        return $ioStyle->confirm('Do you want to skip the shebang?', false);
                    }
                )
            )
            ->addConfig(
                new Definition\Guess(
                    /**
                     * Guess the shebang base on the current platform
                     *
                     * @return bool
                     */
                    function () {
                        return strpos(strtoupper(PHP_OS), 'WINDOWS') !== false;
                    }
                )
            )
            ->addConfig(
                new Definition\FixValue(false)
            );
        $this->validator = new Validator\FixList(array(true, false));
        $this->setBaseError();
    }
}
