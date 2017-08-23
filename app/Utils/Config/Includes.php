<?php

namespace MacFJA\PharBuilder\Utils\Config;

/**
 * Class Includes
 *
 * Configuration of the list of custom path to add to the PHAR
 *
 * @package MacFJA\PharBuilder\Utils\Config
 * @author  MacFJA
 * @license MIT
 */
class Includes extends BaseConfig
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
                    new Definition\Param($this->input, 'include')
                );
        }
        if ($this->readExtra) {
            $this->chain->addConfig(
                new Definition\Composer($this->composerExtra, 'include')
            );
        }
        $this->chain
            ->addConfig(
                new Definition\Guess(
                    /**
                     * Search for common directory
                     *
                     * @param string $baseDir The directory to search in
                     *
                     * @return string[]
                     */
                    function ($baseDir) {
                        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR);

                        $includes = array();

                        /*
                         * Search for:
                         *  - bin/
                         *  - js/
                         *  - css/
                         *  - web/ [symfony|yii2]
                         *  - www/ [nette]
                         *  - public/ [laravel|phalcon|fuel|zf3|zf2]
                         */
                        foreach (array('bin', 'js', 'css', 'web', 'www', 'public') as $dirname) {
                            if (file_exists($baseDir . DIRECTORY_SEPARATOR . $dirname)) {
                                $includes[] = $dirname;
                            }
                        }

                        return $includes;
                    },
                    array($this->composerDir)
                )
            )
            ->addConfig(
                new Definition\FixValue(array())
            );
    }
}
