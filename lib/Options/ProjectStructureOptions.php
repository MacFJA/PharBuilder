<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

/**
 * Class ProjectStructureOptions
 *
 * @codeCoverageIgnore
 * @package MacFJA\PharBuilder\Options
 */
class ProjectStructureOptions implements OptionsInterface
{
    private $rootDir;
    private $configurationOptions;

    /**
     * ProjectStructureOptions constructor.
     *
     * @param $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;

        $common = json_decode(file_get_contents(__DIR__ . '/commonProjectStructure.json'), true);
        $common = array_reduce($common, function ($carry, $item) {
            return array_merge_recursive($carry, $item);
        }, []);
        $common = array_map('array_unique', $common);

        $this->configurationOptions = new ConfigurationOptions($common, $rootDir);
    }

    public function getOutputPath(): ?string
    {
        return $this->rootDir;
    }

    public function getName(): ?string
    {
        return basename($this->rootDir);
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
        return $this->configurationOptions->getEntryPoint();
    }

    public function getIncluded(): ?array
    {
        return $this->configurationOptions->getIncluded();
    }

    public function getExcluded(): array
    {
        return $this->configurationOptions->getExcluded();
    }

    /**
     * @codeCoverageIgnore
     * @return mixed
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function getStubPath(): ?string
    {
        return null;
    }
}
