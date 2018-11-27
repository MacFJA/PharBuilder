<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options\Event;

use League\Event\Event;
use MacFJA\PharBuilder\Composer\ComposerJson;

abstract class PathBasedResultEvent extends Event
{
    private $expectDirectory = false;
    private $expectFile = false;
    /** @var string */
    private $cwd;
    /** @var ComposerJson */
    private $composer;

    /**
     * PathBasedResultEvent constructor.
     *
     * @param string $eventName
     * @param bool $expectDirectory
     * @param bool $expectFile
     * @param string $cwd
     * @param ComposerJson $composer
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $eventName,
        bool $expectDirectory,
        bool $expectFile,
        ?ComposerJson $composer,
        ?string $cwd = null
    ) {
        parent::__construct($eventName);
        if (!$expectFile && !$expectDirectory) {
            throw new \InvalidArgumentException('Both expectDirectory and expectFile can\'t be false');
        }
        $this->expectDirectory = $expectDirectory;
        $this->expectFile = $expectFile;
        $this->cwd = $cwd ?? getcwd();
        $this->composer = $composer;
    }

    public function isPathValid(string $path): bool
    {
        $exists = file_exists($path);

        if (!$exists) {
            return false;
        }

        $typeAllowed = ($this->expectDirectory && is_dir($path)) || ($this->expectFile && is_file($path));

        return $typeAllowed;
    }

    public function getCurrentWorkingDirectory(): string
    {
        return $this->cwd;
    }

    public function getComposerJsonDirectory(): ?string
    {
        if ($this->composer === null || !$this->composer->isValid()) {
            return false;
        }

        return \dirname($this->composer->getPath());
    }
}
