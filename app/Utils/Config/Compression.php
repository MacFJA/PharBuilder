<?php

namespace MacFJA\PharBuilder\Utils\Config;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Compression.
 *
 * Configuration for the compression option (default to No)
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class Compression extends BaseConfig
{
    /**
     * Don't use compression
     */
    const NO = 'No';
    /**
     * Use BZip2 algorithm
     */
    const BZIP2 = 'BZip2';
    /**
     * Use GZip algorithm
     */
    const GZIP = 'GZip';

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
                    new Definition\Param($this->input, 'compression')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'compression')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\SymfonyStyle(
                    $this->input,
                    $this->ioStyle,
                    /**
                     * Ask the wanted compression
                     *
                     * @param SymfonyStyle $ioStyle The Input/Output helper
                     *
                     * @return string
                     */
                    function (SymfonyStyle $ioStyle) {
                        $choice = $ioStyle->choice('Do you want to compress the Phar?', array(
                            'No',
                            'Yes in GZip',
                            'Yes in BZip2'
                        ), 'No');

                        switch ($choice) {
                            case 'No':
                                return self::NO;
                            case 'Yes in GZip':
                                return self::GZIP;
                            case 'Yes in BZip2':
                                return self::BZIP2;
                            default:
                                return self::NO;
                        }
                    }
                )
            )
            ->addConfig(
                new Definition\FixValue(self::NO)
            );

        $this->validator = new Validator\FixList(array(self::NO, self::GZIP, self::BZIP2));
        $this->validator
            ->setIoStyle($this->ioStyle)
            ->setErrorMessages(array(
                'Unknown compression, allowed values are: "' .
                self::NO . '", "' . self::GZIP . '", "' . self::BZIP2 . '"',
                'Value: %s'
            ))
            ->setErrorCode(8);
    }
}
