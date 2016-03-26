<?php

namespace MacFJA\PharBuilder\Event;

use MacFJA\PharBuilder\PharBuilder;

/**
 * Class PharAwareEvent.
 *
 * @package MacFJA\PharBuilder\Event
 * @author  MacFJA
 * @license MIT
 */
class PharAwareEvent extends ComposerAwareEvent
{
    /**
     * The phar builder
     *
     * @var PharBuilder
     */
    protected $pharContext;
    /**
     * Create a new event instance.
     *
     * @param string      $name    The new event name
     * @param PharBuilder $builder The PharBuilder
     */
    public function __construct($name, $builder)
    {
        $this->pharContext = $builder;
        parent::__construct($name, $builder->getComposerReader());
    }

    /**
     * Get the PHAR builder
     *
     * @return PharBuilder
     */
    public function getPhar()
    {
        return $this->pharContext;
    }

    /**
     * Create a new event instance.
     *
     * @param string      $name    The new event name
     * @param PharBuilder $builder The PharBuilder
     *
     * @return static
     */
    public static function named($name, $builder)
    {
        return new static($name, $builder);
    }
}
