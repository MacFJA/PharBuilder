<?php

namespace MacFJA\PharBuilder\Event;

use League\Event\Emitter;
use MacFJA\PharBuilder\Application;
use Neutron\SignalHandler\SignalHandler;

if (!defined('SIGINT')) {
    define('SIGINT', 3);
}

/**
 * Class EventManager.
 * Register handler, send event.
 *
 * @package MacFJA\PharBuilder\Event
 * @author  MacFJA
 * @license MIT
 */
class EventManager extends Emitter
{
    /**
     * Indicate that the Unix Signal is supported by the system and PHP
     */
    const SIGNAL_COMP_OK = 'ok';
    /**
     * Indicate that the Unix Signal is not supported because we are on a Windows System
     */
    const SIGNAL_COMP_WINDOWS = 'windows';
    /**
     * Indicate that the Unix Signal is not supported because PHP has not been compiled with signal support
     */
    const SIGNAL_COMP_COMPILATION = 'compilation';

    /**
     * The current Phar-Builder application
     *
     * @var Application
     */
    protected $application;

    /**
     * EventManager constructor.
     *
     * @param Application $application The Phar-Builder application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Check is the current system and PHP support UNIX signal
     *
     * @return string
     */
    public function isSignalCompatible()
    {
        if (strpos(strtolower(PHP_OS), 'windows') === false) {
            if (!function_exists('pcntl_signal')) {
                return self::SIGNAL_COMP_COMPILATION;
            }
        } else {
            return self::SIGNAL_COMP_WINDOWS;
        }

        return self::SIGNAL_COMP_OK;
    }

    /**
     * Register the application event handler and the Unix signal handler
     *
     * @return void
     */
    public function registerApplication()
    {
        $this->addListener('*', new ApplicationListener());
        if (function_exists('pcntl_signal')) {
            SignalHandler::getInstance()->register(SIGINT, array($this, 'unixSignalHandler'));
        }
    }

    /**
     * The Unix signal handler. (Re-send to the application event handler)
     *
     * @param int $signal The Unix signal code
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression) -- Normal/Wanted behavior
     */
    public function unixSignalHandler($signal)
    {
        $eventName = 'unix.unk';
        switch ($signal) {
            case SIGINT:
                $eventName = 'unix.interrupt';
                break;
        }
        $this->emit(new PharAwareEvent($eventName, $this->application->getBuilder()));
        exit(128 + $signal);
    }
}
