<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Composer;

class ComposerLock
{
    use ComposerAutoloaderTrait;

    private const FILE_NAME = 'composer.lock';
    /** @var string */
    private $path;
    /** @var ComposerJson */
    private $composerJson;
    /** @var array */
    private $data;

    /**
     * ComposerJson constructor.
     *
     * @param ComposerJson $composerJson
     */
    public function __construct(ComposerJson $composerJson)
    {
        $this->composerJson = $composerJson;

        if (!$composerJson->isValid()) {
            $this->data = [];

            return;
        }

        $this->path = \dirname($composerJson->getPath()) . DIRECTORY_SEPARATOR . self::FILE_NAME;

        if (!file_exists($this->path) || !is_readable($this->path) || !is_file($this->path)) {
            $this->data = [];

            return;
        }

        $rawContent = file_get_contents($this->path);

        if ($rawContent === false) {
            $this->data = [];

            return;
        }

        $this->data = json_decode($rawContent, true);
        if (!\is_array($this->data)) {
            $this->data = [];
        }
    }

    public function getRequirePath(): array
    {
        $package = $this->data['packages'] ?? [];
        $autoloads = array_column($package, 'autoload', 'name');

        $psrPath = $this->getPsrPath($autoloads);
        $filesPath = $this->getFilesPath($autoloads, true);

        $vendorDir = $this->composerJson->getVendorDir();

        $allPath = array_merge($psrPath, $filesPath);
        $allPath = array_map(function (string $path) use ($vendorDir): string {
            return $vendorDir . DIRECTORY_SEPARATOR . $path;
        }, $allPath);

        return $allPath;
    }

    public function getRequireDevPath(): array
    {
        $package = $this->data['packages-dev'] ?? [];
        $autoloads = array_column($package, 'autoload', 'name');

        $psrPath = $this->getPsrPath($autoloads);
        $filesPath = $this->getFilesPath($autoloads, true);

        $vendorDir = $this->composerJson->getVendorDir();

        $allPath = array_merge($psrPath, $filesPath);
        $allPath = array_map(function (string $path) use ($vendorDir): string {
            return $vendorDir . DIRECTORY_SEPARATOR . $path;
        }, $allPath);

        return $allPath;
    }

    public function getRequireDevFilesAutoload(): array
    {
        $package = $this->data['packages-dev'] ?? [];
        $autoloads = array_column($package, 'autoload', 'name');

        $filesPath = $this->getFilesPath($autoloads, false);

        $vendorDir = $this->composerJson->getVendorDir();

        $filesPath = array_map(function (string $path) use ($vendorDir): string {
            return $vendorDir . DIRECTORY_SEPARATOR . $path;
        }, $filesPath);

        return $filesPath;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
