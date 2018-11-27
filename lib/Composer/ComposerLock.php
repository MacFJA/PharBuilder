<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Composer;

class ComposerLock
{
    private const FILE_NAME = 'composer.lock';
    /** @var string */
    private $path;
    /** @var ComposerJson */
    private $composerJson;

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
        $psrPath = $this->getPsrPath(false);
        $filesPath = $this->getFilesPath(false, true);

        $vendorDir = $this->composerJson->getVendorDir();

        $allPath = array_merge($psrPath, $filesPath);
        $allPath = array_map(function (string $path) use ($vendorDir): string {
            return $vendorDir . DIRECTORY_SEPARATOR . $path;
        }, $allPath);

        return $allPath;
    }

    /**
     * @codeCoverageIgnore
     * @param bool $dev
     * @return string[]
     */
    private function getPsrPath(bool $dev): array
    {
        $package = $this->data['packages' . ($dev ? '-dev' : '')] ?? [];

        $autoloads = array_column($package, 'autoload', 'name');

        $allPath = [];

        foreach ($autoloads as $name => $autoload) {
            $prefix = $name;

            // PSR-0 and PSR-4 are the same if the namespace doesn't matter
            // And for us, it's the case
            $psrPath = array_merge($autoload['psr-4'] ?? [], $autoload['psr-0'] ?? []);
            // Only need the path (remove the namespace)
            $psrPath = array_values($psrPath);
            // Flatten path, as you can specify multiple path for one namespace
            $psrPath = array_reduce($psrPath, function ($carry, $item): array {
                if (\is_array($item)) {
                    return array_merge($carry, $item);
                }
                $carry[] = $item;

                return $carry;
            }, []);
            // Add the package name (<=> path in vendor dir)
            $psrPath = array_map(function (string $path) use ($prefix) {
                return $prefix . DIRECTORY_SEPARATOR . $path;
            }, $psrPath);

            $allPath = array_merge($allPath, $psrPath);
        }

        return $allPath;
    }

    /**
     * @codeCoverageIgnore
     * @param bool $dev
     * @param bool $withClassmap
     * @return string[]
     */
    private function getFilesPath(bool $dev, bool $withClassmap): array
    {
        $package = $this->data['packages' . ($dev ? '-dev' : '')] ?? [];

        $autoloads = array_column($package, 'autoload', 'name');

        $allPath = [];

        foreach ($autoloads as $name => $autoload) {
            $prefix = $name;

            $filePath = $autoload['files'] ?? [];
            // "files" and "classmap" have a very similar behavior
            if ($withClassmap) {
                $filePath = array_merge($filePath, $autoload['classmap'] ?? []);
            }
            // Flatten path, as you can specify multiple path for one namespace
            $filePath = array_reduce($filePath, function ($carry, $item): array {
                if (\is_array($item)) {
                    return array_merge($carry, $item);
                }
                $carry[] = $item;

                return $carry;
            }, []);
            // Add the package name (<=> path in vendor dir)
            $filePath = array_map(function (string $path) use ($prefix) {
                return $prefix . DIRECTORY_SEPARATOR . $path;
            }, $filePath);

            $allPath = array_merge($allPath, $filePath);
        }

        return $allPath;
    }

    public function getRequireDevPath()
    {
        $psrPath = $this->getPsrPath(true);
        $filesPath = $this->getFilesPath(true, true);

        $vendorDir = $this->composerJson->getVendorDir();

        $allPath = array_merge($psrPath, $filesPath);
        $allPath = array_map(function (string $path) use ($vendorDir): string {
            return $vendorDir . DIRECTORY_SEPARATOR . $path;
        }, $allPath);

        return $allPath;
    }

    public function getRequireDevFilesAutoload()
    {
        $filesPath = $this->getFilesPath(true, false);

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
