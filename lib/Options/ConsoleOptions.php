<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class ConsoleOptions implements OptionsInterface
{
    use OptionsHelperTrait;

    public const OUTPUT_OPTION_NAME = 'output';
    public const NAME_OPTION_NAME = 'name';
    public const WITHOUT_DEV_OPTION_NAME = 'no-dev';
    public const WITH_DEV_OPTION_NAME = 'dev';
    public const WITHOUT_SHEBANG_OPTION_NAME = 'no-shebang';
    public const WITH_SHEBANG_OPTION_NAME = 'shebang';
    public const ENTRYPOINT_OPTION_NAME = 'entry-point';
    public const INCLUDED_OPTION_NAME = 'included';
    public const EXCLUDED_OPTION_NAME = 'excluded';
    public const BZ_COMPRESSION_OPTION_NAME = 'bz2';
    public const GZ_COMPRESSION_OPTION_NAME = 'gzip';
    public const NO_COMPRESSION_OPTION_NAME = 'flat';

    /** @var InputInterface */
    private $input;
    /** @var string */
    private $rootDir;

    /**
     * ConsoleOptions constructor.
     *
     * @param InputInterface $input
     * @param string         $rootDir
     */
    public function __construct(InputInterface $input, string $rootDir)
    {
        $this->input = $input;
        $this->rootDir = $rootDir;
    }

    /**
     * @return InputDefinition
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public static function getInputDefinition(): InputDefinition
    {
        $def = new InputDefinition();
        $def->addOptions([
            new InputOption(self::OUTPUT_OPTION_NAME, null, InputOption::VALUE_REQUIRED, 'Where to output the Phar'),
            new InputOption(self::NAME_OPTION_NAME, null, InputOption::VALUE_REQUIRED, 'The name of the Phar'),
            new InputOption(self::WITHOUT_DEV_OPTION_NAME, null, InputOption::VALUE_NONE, 'Do not include dev dependencies'),
            new InputOption(self::WITH_DEV_OPTION_NAME, null, InputOption::VALUE_NONE, 'Include dev dependencies'),
            new InputOption(self::WITHOUT_SHEBANG_OPTION_NAME, null, InputOption::VALUE_NONE, 'Do not add/remove shebang'),
            new InputOption(self::WITH_SHEBANG_OPTION_NAME, null, InputOption::VALUE_NONE, 'Ensure that a shebang is used'),
            new InputOption(self::ENTRYPOINT_OPTION_NAME, null, InputOption::VALUE_REQUIRED, 'The file to include when the Phar is executed/called'),
            new InputOption(self::INCLUDED_OPTION_NAME, null, InputOption::VALUE_REQUIRED, 'The list (separate by ",") of path to add in the Phar'),
            new InputOption(self::EXCLUDED_OPTION_NAME, null, InputOption::VALUE_REQUIRED, 'The list (separate by ",") of path to exclude in the Phar'),
            new InputOption(self::BZ_COMPRESSION_OPTION_NAME, null, InputOption::VALUE_NONE, 'Use the BZip2 compression for the Phar'),
            new InputOption(self::GZ_COMPRESSION_OPTION_NAME, null, InputOption::VALUE_NONE, 'Use the GZip compression for the Phar'),
            new InputOption(self::NO_COMPRESSION_OPTION_NAME, null, InputOption::VALUE_NONE, 'Do not compress the Phar'),
        ]);

        return $def;
    }

    public function getOutputPath(): ?string
    {
        return $this->getOnePathData($this->getOption(static::OUTPUT_OPTION_NAME), $this->rootDir, false, true);
    }

    private function getOption($name)
    {
        return $this->input->hasOption($name) ? $this->input->getOption($name) : null;
    }

    public function getName(): ?string
    {
        return $this->getOption(static::NAME_OPTION_NAME);
    }

    public function includeDev(): ?bool
    {
        return $this->getBoolOption(static::WITH_DEV_OPTION_NAME, static::WITHOUT_DEV_OPTION_NAME);
    }

    private function getBoolOption($withName, $withoutName): ?bool
    {
        $without = $this->getOption($withoutName);
        $with = $this->getOption($withName);

        if ($with) {
            return true;
        }

        if ($without) {
            return false;
        }

        return null;
    }

    public function getCompression(): ?int
    {
        $bz = $this->getOption(static::BZ_COMPRESSION_OPTION_NAME);
        $gz = $this->getOption(static::GZ_COMPRESSION_OPTION_NAME);
        $none = $this->getOption(static::NO_COMPRESSION_OPTION_NAME);

        switch (true) {
            case $bz:
                return \Phar::BZ2;
            case $gz:
                return \Phar::GZ;
            case $none:
                return \Phar::NONE;
            default:
                return null;
        }
    }

    public function getEntryPoint(): ?string
    {
        return $this->getOnePathData($this->getOption(static::ENTRYPOINT_OPTION_NAME), $this->rootDir, true, false);
    }

    public function getIncluded(): ?array
    {
        $value = $this->getOption(static::INCLUDED_OPTION_NAME);
        if ($value === null) {
            return null;
        }

        return $this->getPathsData(explode(',', (string)$value), $this->rootDir);
    }

    public function getExcluded(): array
    {
        $value = $this->getOption(static::EXCLUDED_OPTION_NAME);
        if ($value === null || empty($value)) {
            return [];
        }

        return explode(',', (string)$value);
    }

    /**
     * @codeCoverageIgnore
     * @return InputInterface
     */
    public function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function getStubPath(): ?string
    {
        $shebang = $this->getBoolOption(static::WITH_SHEBANG_OPTION_NAME, static::WITHOUT_SHEBANG_OPTION_NAME);
        if ($shebang === null) {
            return null;
        }

        if ($shebang) {
            return \dirname(__DIR__, 2) . '/resources/stubs/map-phar+shebang.tpl';
        }

        return \dirname(__DIR__, 2) . '/resources/stubs/map-phar+no-shebang.tpl';
    }
}
