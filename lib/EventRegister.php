<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder;

use League\Event\AbstractListener;
use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;
use League\Event\EventInterface;
use League\Event\ListenerInterface;

class EventRegister extends AbstractListener implements EmitterAwareInterface
{
    use EmitterAwareTrait;

    /**
     * @var array<string,array>
     */
    private $listener = [];

    public function addEventHandler(string $eventName, callable $callable): void
    {
        if (strpos($eventName, '*') !== false) {
            $this->addWildcard($eventName, $callable);

            return;
        }

        $this->getEmitter()->addListener($eventName, $callable);
    }

    /**
     * @param string $eventName
     * @param mixed  $object
     */
    private function addWildcard(string $eventName, $object): void
    {
        if (!array_key_exists($eventName, $this->listener)) {
            $this->listener[$eventName] = [];
        }

        $this->listener[$eventName][] = $object;
    }

    public function addEventListener(string $eventName, ListenerInterface $listener): void
    {
        if (strpos($eventName, '*') !== false) {
            $this->addWildcard($eventName, $listener);

            return;
        }

        $this->getEmitter()->addListener($eventName, $listener);
    }

    /**
     * Handle an event.
     *
     * @param EventInterface $event
     *
     * @return void
     */
    public function handle(EventInterface $event)
    {
        $events = array_filter($this->listener, function (string $eventName) use ($event): bool {
            return fnmatch($eventName, $event->getName());
        }, ARRAY_FILTER_USE_KEY);

        $events = array_reduce($events, 'array_merge', []);

        array_walk(
            $events,
            /**
             * @param mixed $item
             * @return void
             */
            function ($item) use ($event) {
                if ($item instanceof ListenerInterface) {
                    $item->handle($event);

                    return;
                }
                if (\is_callable($item)) {
                    $item($event);
                }
            }
        );
    }
}
