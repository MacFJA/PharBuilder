<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

class DefaultOptions implements OptionsInterface
{

    public function getOutputPath(): ?string
    {
        return \dirname(getcwd());
    }

    public function getName(): ?string
    {
        throw new \BadMethodCallException('The name of the phar MUST be defined.');
    }

    public function includeDev(): ?bool
    {
        return false;
    }

    public function getCompression(): ?int
    {
        return \Phar::NONE;
    }

    public function getEntryPoint(): ?string
    {
        throw new \BadMethodCallException('The entry point of the phar MUST be defined.');
    }

    public function getIncluded(): ?array
    {
        return [];
    }

    public function getExcluded(): array
    {
        return [];
    }

    public function getStubPath(): ?string
    {
        return \dirname(__DIR__, 2) . '/resources/stubs/map-phar+shebang.tpl';
    }
}
