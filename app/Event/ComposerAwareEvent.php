<?php

namespace MacFJA\PharBuilder\Event;

use League\Event\Event;
use MacFJA\PharBuilder\Utils\Composer;

/**
 * Class ComposerAwareEvent.
 *
 * @package MacFJA\PharBuilder\Event
 * @author  MacFJA
 * @license MIT
 */
class ComposerAwareEvent extends Event
{
    /**
     * The phar builder
     *
     * @var Composer
     */
    protected $composerReader;
    /**
     * Create a new event instance.
     *
     * @param string   $name     The new event name
     * @param Composer $composer The PharBuilder
     */
    public function __construct($name, $composer)
    {
        $this->composerReader = $composer;
        parent::__construct($name);
    }

    /**
     * Get the composer reader object
     *
     * @return Composer
     */
    public function getComposerReader()
    {
        return $this->composerReader;
    }

    /**
     * Create a new event instance.
     *
     * @param string   $name     The new event name
     * @param Composer $composer The PharBuilder
     *
     * @return static
     */
    public static function named($name, $composer)
    {
        return new static($name, $composer);
    }
}
