<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder;

use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use MacFJA\PharBuilder\Builder\Event\BuilderAddEvent;
use MacFJA\PharBuilder\Builder\Event\CompressorEvent;
use MacFJA\PharBuilder\Builder\Event\StatsEvent;
use MacFJA\PharBuilder\Command\Package;
use Symfony\Component\Console\Application as SfApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Application extends SfApplication implements EmitterAwareInterface, ListenerInterface
{
    use EmitterAwareTrait;

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     * @var SymfonyStyle
     */
    private $style;

    public function __construct()
    {
        parent::__construct('Phar Builder');
        $this->add((new Package('package'))->setEmitter($this->getEmitter()));
        $this->setDefaultCommand('package');
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
        if ($event instanceof StatsEvent) {
            $this->writeProgressionLine('');
            $this->style->success('Phar creation successful');
            $this->style->table([], [
                ['Path', '<info>' . $event->getStats()->getFinalPath() . '</info>'],
                ['File size', '<info>' . $event->getSize() . '</info>'],
                ['Process duration', '<info>' . $event->getDuration() . '</info>']
            ]);
        }

        if ($event instanceof BuilderAddEvent) {
            $this->writeProgressionLine('Adding <comment>' . $event->getPath() . '</comment>');
        }

        if ($event instanceof CompressorEvent) {
            $this->writeProgressionLine('<info>Compressing...</info>');
        }

        if (0 === strpos($event->getName(), 'options.')) {
            $this->writeProgressionLine(
                '<comment>Read options...</comment> (' . ucfirst(substr($event->getName(), \strlen('options.'))) . ')',
                true
            );
        }
    }

    private function writeProgressionLine(string $message, bool $debugOnly = false): void
    {
        if ($debugOnly && OutputInterface::VERBOSITY_DEBUG !== $this->style->getVerbosity()) {
            return;
        }

        $before = "\r\033[K";
        $after = '';

        if (\in_array(
            $this->style->getVerbosity(),
            [
                OutputInterface::VERBOSITY_DEBUG,
                OutputInterface::VERBOSITY_VERY_VERBOSE,
                OutputInterface::VERBOSITY_VERBOSE
            ],
            true
        )) {
            $before = '';
            $after = "\n";
        }

        $this->style->write($before . $message . $after);
    }

    /**
     * Check whether the listener is the given parameter.
     *
     * @param mixed $listener
     *
     * @return bool
     */
    public function isListener($listener)
    {
        return $listener === $this;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return void
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);

        $this->style = new SymfonyStyle($input, $output);

        $this->getEmitter()->addListener('*', $this);
    }
}
