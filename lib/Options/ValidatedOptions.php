<?php
/* Copyright (C) 2019 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

use MacFJA\PharBuilder\Exception\MissingConfigurationException;

/**
 * @codeCoverageIgnore
 */
final class ValidatedOptions
{
    /** @var array */
    private $optionsCache = [];
    /** @var OptionsInterface */
    private $parentOptions;

    /**
     * ValidatedOptions constructor.
     *
     * @param OptionsInterface $parentOptions
     */
    public function __construct(OptionsInterface $parentOptions)
    {
        $this->parentOptions = $parentOptions;
    }

    /**
     * @param string $functionName
     * @param string $message
     *
     * @return mixed
     */
    private function getOptionalOption(string $functionName, string $message)
    {
        if (!array_key_exists($functionName, $this->optionsCache)) {
            $option = $this->parentOptions->{$functionName}();

            if ($option === null) {
                throw new MissingConfigurationException($message);
            }

            $this->optionsCache[$functionName] = $option;
        }

        return $this->optionsCache[$functionName];
    }

    /**
     * @param string $functionName
     *
     * @return mixed
     */
    private function getOption(string $functionName)
    {
        if (!array_key_exists($functionName, $this->optionsCache)) {
            $this->optionsCache[$functionName] = $this->parentOptions->{$functionName}();
        }

        return $this->optionsCache[$functionName];
    }

    public function getOutputPath(): string
    {
        return $this->getOptionalOption(__FUNCTION__, 'The output directory of the phar');
    }

    public function getName(): string
    {
        return $this->getOptionalOption(__FUNCTION__, 'The name of the phar');
    }

    public function includeDev(): bool
    {
        return $this->getOptionalOption(__FUNCTION__, 'The inclusion of dev dependencies');
    }

    public function getCompression(): int
    {
        return $this->getOptionalOption(__FUNCTION__, 'The compression of the phar');
    }

    public function getEntryPoint(): string
    {
        return $this->getOptionalOption(__FUNCTION__, 'The entry point of the phar');
    }

    public function getIncluded(): array
    {
        return $this->getOptionalOption(__FUNCTION__, 'The additional files of the phar');
    }

    public function getExcluded(): array
    {
        return $this->getOption(__FUNCTION__);
    }

    public function getStubPath(): string
    {
        return $this->getOptionalOption(__FUNCTION__, 'The stub file of the phar');
    }
}
