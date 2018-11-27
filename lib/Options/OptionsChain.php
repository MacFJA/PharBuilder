<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

use League\Event\EmitterInterface;
use MacFJA\PharBuilder\Composer\ComposerJson;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class OptionsChain
 *
 * @codeCoverageIgnore
 * @package MacFJA\PharBuilder\Options
 */
class OptionsChain implements OptionsInterface
{
    /** @var OptionsInterface[] */
    private $options = [];

    /**
     * OptionsChain constructor.
     *
     * @param OptionsInterface[] $options
     */
    public function __construct(array $options)
    {
        /*
         * Use PHP to check is typing is correct
         */
        foreach ($options as $option) {
            $this->addOptions($option);
        }
    }

    public function addOptions(OptionsInterface $options, int $position = -1): void
    {
        if ($position < 0 || $position >= \count($this->options)) {
            $this->options[] = $options;

            return;
        }

        array_splice($this->options, $position, 0, $options);
    }

    public static function createDefaultChain(
        EmitterInterface $emitter,
        ?InputInterface $consoleInput = null,
        ?ComposerJson $composerJson = null,
        ?string $rootDir = null
    ): OptionsChain {
        $chain = [new EventOptions($emitter, $composerJson, $rootDir)];

        if ($composerJson !== null && $rootDir === null) {
            $rootDir = \dirname($composerJson->getPath());
        }

        if ($consoleInput !== null && $rootDir !== null) {
            $chain[] = new ConsoleOptions($consoleInput, $rootDir);
        }
        if ($composerJson !== null) {
            $chain[] = new ConfigurationOptions($composerJson->getExtra('phar-builder'), $rootDir);
            $chain[] = new ComposerOptions($composerJson);
        }
        if ($rootDir !== null) {
            $chain[] = new ProjectStructureOptions($rootDir);
        }
        $chain[] = new DefaultOptions();

        return new static($chain);
    }

    public function getOutputPath(): ?string
    {
        return $this->recursiveCall(__FUNCTION__);
    }

    private function recursiveCall($name)
    {
        foreach ($this->options as $option) {
            $value = $option->{$name}();
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    public function getName(): ?string
    {
        return $this->recursiveCall(__FUNCTION__);
    }

    public function includeDev(): ?bool
    {
        return $this->recursiveCall(__FUNCTION__);
    }

    public function getCompression(): ?int
    {
        return $this->recursiveCall(__FUNCTION__);
    }

    public function getEntryPoint(): ?string
    {
        return $this->recursiveCall(__FUNCTION__);
    }

    public function getIncluded(): ?array
    {
        return $this->recursiveCall(__FUNCTION__);
    }

    public function getExcluded(): array
    {
        $allValues = [];
        foreach ($this->options as $option) {
            $value = $option->getExcluded();
            if ($value === null || \count($value) === 0) {
                continue;
            }
            $allValues = array_merge($allValues, $value);
        }

        return $allValues;
    }

    /**
     * @codeCoverageIgnore
     * @return OptionsInterface[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getStubPath(): ?string
    {
        return $this->recursiveCall(__FUNCTION__);
    }
}
