<?php

namespace MacFJA\PharBuilder\Utils\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class IncludeDev
 *
 * Configuration reader about the fact if development package must be included
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class IncludeDev extends BaseConfig
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
                    new Definition\Param($this->input, 'include-dev')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'include-dev')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\SymfonyStyle(
                    $this->input,
                    $this->ioStyle,
                    /**
                     * Ask if we want to include dev package
                     *
                     * @param SymfonyStyle $ioStyle The Input/Output helper
                     *
                     * @return bool
                     */
                    function (SymfonyStyle $ioStyle) {
                        return $ioStyle->confirm('Do you want to include dev?', false);
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
