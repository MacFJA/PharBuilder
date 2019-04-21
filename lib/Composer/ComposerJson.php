<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Composer;

class ComposerJson
{
    use ComposerAutoloaderTrait;

    private const FILE_NAME = 'composer.json';
    /** @var string */
    private $path;
    /** @var array */
    private $data;
    /** @var bool */
    private $valid = true;
    /** @var ComposerLock|null */
    private $lockObject;

    /**
     * ComposerJson constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;

        if (!file_exists($path)
            || !is_readable($path)
            || !is_file($path)
            || substr($path, -\strlen(self::FILE_NAME)) !== self::FILE_NAME
        ) {
            $this->valid = false;
            $this->data = [];

            return;
        }

        $rawContent = file_get_contents($path);

        if ($rawContent === false) {
            $this->valid = false;
            $this->data = [];

            return;
        }

        $this->data = json_decode($rawContent, true);
        if (!\is_array($this->data)) {
            $this->valid = false;
            $this->data = [];
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): ?string
    {
        return $this->data['name'] ?? null;
    }

    public function getBins(): array
    {
        return $this->data['bin'] ?? [];
    }

    public function getVendorDir(): string
    {
        return $this->data['config']['vendor-dir'] ?? 'vendor';
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getExtra(string $string): array
    {
        return $this->data['extra'][$string] ?? [];
    }

    /**
     * @codeCoverageIgnore
     * @return ComposerLock
     */
    public function getComposerLock(): ComposerLock
    {
        if ($this->lockObject === null) {
            $this->lockObject = new ComposerLock($this);
        }

        return $this->lockObject;
    }

    public function getSourcesPath(bool $includeDev): array
    {
        $allPath = $this->getAutoloadsPath(false);
        if ($includeDev) {
            $allPath = array_merge($allPath, $this->getAutoloadsPath(true));
        }

        return array_unique($allPath);
    }

    private function getAutoloadsPath(bool $dev): array
    {
        $autoloads = [$this->data['autoload' . ($dev ? '-dev' : '')] ?? []];

        $psrPath = $this->getPsrPath($autoloads);
        $filesPath = $this->getFilesPath($autoloads, true);

        $allPath = array_merge($psrPath, $filesPath);
        $allPath = array_map(function (string $path): string {
            return dirname($this->getPath()) . DIRECTORY_SEPARATOR . $path;
        }, $allPath);

        return $allPath;
    }
}
