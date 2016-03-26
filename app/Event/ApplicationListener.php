<?php

namespace MacFJA\PharBuilder\Event;

use League\Event\AbstractListener;
use League\Event\EventInterface;
use Symfony\Component\Process\Process;

/**
 * Class ApplicationListener.
 *
 * @package Event
 * @author  MacFJA
 * @license MIT
 */
class ApplicationListener extends AbstractListener
{

    /**
     * Handle an event.
     *
     * @param EventInterface $event The event to handle
     *
     * @return void
     */
    public function handle(EventInterface $event)
    {
        if (!($event instanceof ComposerAwareEvent)) {
            return;
        }
        $data = $event->getComposerReader()->getComposerConfig();

        if (!array_key_exists('events', $data) || !array_key_exists($event->getName(), $data['events'])) {
            return;
        }

        $actions = $data['events'][$event->getName()];
        if (!is_array($actions)) {
            $actions = array($actions);
        }

        foreach ($actions as $action) {
            $process = new Process($action, dirname($event->getComposerReader()->getComposerJsonPath()));
            $process->run();
        }
    }
}
