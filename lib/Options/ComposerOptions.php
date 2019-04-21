<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

use MacFJA\PharBuilder\Composer\ComposerJson;

class ComposerOptions implements OptionsInterface
{
    /** @var ComposerJson */
    private $composer;

    /**
     * ComposerOptions constructor.
     *
     * @param ComposerJson $composer
     */
    public function __construct(ComposerJson $composer)
    {
        $this->composer = $composer;
    }

    public static function createFromPath(string $path): self
    {
        return new static(new ComposerJson($path));
    }

    public function getOutputPath(): ?string
    {
        return \dirname($this->composer->getPath());
    }

    public function getName(): ?string
    {
        $name = $this->composer->getName();
        if ($name === null) {
            return null;
        }
        list(, $name) = explode('/', $name);

        return $name;
    }

    public function includeDev(): ?bool
    {
        return null;
    }

    public function getCompression(): ?int
    {
        return null;
    }

    public function getEntryPoint(): ?string
    {
        $bin = $this->composer->getBins();
        if (count($bin) === 0) {
            return null;
        }

        return reset($bin);
    }

    public function getIncluded(): ?array
    {
        return null;
    }

    public function getExcluded(): array
    {
        return [];
    }

    public function getStubPath(): ?string
    {
        return null;
    }
}
