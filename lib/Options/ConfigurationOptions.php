<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

class ConfigurationOptions implements OptionsInterface
{
    use OptionsHelperTrait;

    public const OUTPUT_OPTION_NAME = 'output';
    public const NAME_OPTION_NAME = 'name';
    public const WITH_DEV_OPTION_NAME = 'dev';
    public const WITH_SHEBANG_OPTION_NAME = 'shebang';
    public const ENTRYPOINT_OPTION_NAME = 'entry-point';
    public const INCLUDED_OPTION_NAME = 'included';
    public const EXCLUDED_OPTION_NAME = 'excluded';
    public const COMPRESSION_OPTION_NAME = 'compression';
    public const BZ_COMPRESSION_OPTION_VALUE = 'bz2';
    public const GZ_COMPRESSION_OPTION_VALUE = 'gzip';
    public const NO_COMPRESSION_OPTION_VALUE = 'none';

    /** @var array<string,mixed> */
    private $configurations;
    /** @var string */
    private $rootDir;

    /**
     * ConfigurationOptions constructor.
     *
     * @param array  $configurations
     * @param string $rootDir
     */
    public function __construct(array $configurations, string $rootDir)
    {
        $this->configurations = $configurations;
        $this->rootDir = $rootDir;
    }

    public function getOutputPath(): ?string
    {
        return $this->getOnePathData($this->getData(static::OUTPUT_OPTION_NAME), $this->rootDir, false, true);
    }

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    private function getData(string $path)
    {
        if (empty($this->configurations)) {
            return null;
        }

        return $this->configurations[$path] ?? null;
    }

    public function getName(): ?string
    {
        return $this->getData(static::NAME_OPTION_NAME);
    }

    public function includeDev(): ?bool
    {
        return $this->getBooleanData(static::WITH_DEV_OPTION_NAME);
    }

    private function getBooleanData(string $name): ?bool
    {
        $data = $this->getData($name);

        if ($data === null) {
            return null;
        }

        if ($data === 'true' || $data === true || $data === 1) {
            return true;
        }

        if ($data === 'false' || $data === false || $data === 0) {
            return false;
        }

        return null;
    }

    public function getCompression(): ?int
    {
        $data = $this->getData(static::COMPRESSION_OPTION_NAME);

        switch ($data) {
            case static::BZ_COMPRESSION_OPTION_VALUE:
                return \Phar::BZ2;
            case static::GZ_COMPRESSION_OPTION_VALUE:
                return \Phar::GZ;
            case static::NO_COMPRESSION_OPTION_VALUE:
                return \Phar::NONE;
            default:
                return null;
        }
    }

    public function getEntryPoint(): ?string
    {
        return $this->getOnePathData($this->getData(static::ENTRYPOINT_OPTION_NAME), $this->rootDir, true, false);
    }

    public function getIncluded(): ?array
    {
        return $this->getPathsData($this->getData(static::INCLUDED_OPTION_NAME), $this->rootDir);
    }

    public function getExcluded(): array
    {
        $data = $this->getData(static::EXCLUDED_OPTION_NAME);

        return $data ?? [];
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function getConfigurations(): array
    {
        return $this->configurations;
    }

    public function getStubPath(): ?string
    {
        $shebang = $this->getBooleanData(static::WITH_SHEBANG_OPTION_NAME);
        if ($shebang === null) {
            return null;
        }
        if ($shebang) {
            return \dirname(__DIR__, 2) . '/resources/stubs/map-phar+shebang.tpl';
        }

        return \dirname(__DIR__, 2) . '/resources/stubs/map-phar+no-shebang.tpl';
    }
}
