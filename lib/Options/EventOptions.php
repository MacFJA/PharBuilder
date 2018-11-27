<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

use League\Event\EmitterInterface;
use MacFJA\PharBuilder\Composer\ComposerJson;
use MacFJA\PharBuilder\Options\Event\BooleanResultEvent;
use MacFJA\PharBuilder\Options\Event\IntegerResultEvent;
use MacFJA\PharBuilder\Options\Event\PathListResultEvent;
use MacFJA\PharBuilder\Options\Event\PathResultEvent;
use MacFJA\PharBuilder\Options\Event\StringResultEvent;

class EventOptions implements OptionsInterface
{
    /** @var EmitterInterface */
    private $emitter;
    /** @var ComposerJson */
    private $composer;
    /**
     * @var null|string
     */
    private $rootDir;

    /**
     * EventOptions constructor.
     *
     * @param EmitterInterface  $emitter
     * @param ComposerJson|null $composer
     * @param null|string       $rootDir
     */
    public function __construct(EmitterInterface $emitter, ?ComposerJson $composer, ?string $rootDir)
    {
        $this->emitter = $emitter;
        $this->composer = $composer;
        $this->rootDir = $rootDir;
    }

    public function getOutputPath(): ?string
    {
        $event = $this->emitter->emit(new PathResultEvent(
            'options.output',
            true,
            false,
            $this->composer,
            $this->rootDir
        ));
        if ($event instanceof PathResultEvent) {
            return $event->getPath();
        }

        return null;
    }

    public function getName(): ?string
    {
        $event = $this->emitter->emit(new StringResultEvent('options.name'));
        if ($event instanceof StringResultEvent) {
            return $event->getResult();
        }

        return null;
    }

    public function includeDev(): ?bool
    {
        $event = $this->emitter->emit(new BooleanResultEvent('options.include-dev'));
        if ($event instanceof BooleanResultEvent) {
            return $event->getResult();
        }

        return null;
    }

    public function getStubPath(): ?string
    {
        $event = $this->emitter->emit(new PathResultEvent(
            'options.stub-path',
            false,
            true,
            $this->composer,
            $this->rootDir
        ));
        if ($event instanceof PathResultEvent) {
            return $event->getPath();
        }

        return null;
    }

    public function getCompression(): ?int
    {
        $event = $this->emitter->emit(new IntegerResultEvent('options.compression'));
        if ($event instanceof IntegerResultEvent) {
            return $event->getResult();
        }

        return null;
    }

    public function getEntryPoint(): ?string
    {
        $event = $this->emitter->emit(new PathResultEvent(
            'options.entry-point',
            false,
            true,
            $this->composer,
            $this->rootDir
        ));
        if ($event instanceof StringResultEvent) {
            return $event->getResult();
        }

        return null;
    }

    public function getIncluded(): ?array
    {
        $event = $this->emitter->emit(new PathListResultEvent(
            'options.included',
            true,
            true,
            $this->composer,
            $this->rootDir
        ));
        if ($event instanceof PathListResultEvent) {
            return $event->getPaths();
        }

        return null;
    }

    public function getExcluded(): array
    {
        $event = $this->emitter->emit(new PathListResultEvent(
            'options.excluded',
            true,
            true,
            $this->composer,
            $this->rootDir
        ));
        if ($event instanceof PathListResultEvent) {
            return $event->getPaths() ?? [];
        }

        return [];
    }
}
