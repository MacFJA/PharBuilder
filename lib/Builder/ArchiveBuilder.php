<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder;

use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;
use MacFJA\PharBuilder\Builder\Event\BuilderAddEvent;
use RecursiveIteratorIterator;

class ArchiveBuilder implements EmitterAwareInterface
{
    use EmitterAwareTrait;

    /** @var \Phar */
    private $phar;
    /** @var PathVisitor */
    private $visitor;
    /**
     * @var string
     */
    private $projectPath;

    /**
     * ArchiveBuilder constructor.
     *
     * @param \Phar       $phar
     * @param PathVisitor $visitor
     * @param string      $projectPath
     */
    public function __construct(\Phar $phar, PathVisitor $visitor, string $projectPath)
    {
        $this->phar = $phar;
        $this->visitor = $visitor;
        $this->projectPath = $projectPath;
    }

    /**
     * @return \Phar
     */
    public function getPhar(): \Phar
    {
        return $this->phar;
    }

    public function add(string $path): void
    {
        $path = str_replace('/./', '/', $path);

        if (is_dir($path)) {
            $this->addDirectory($path);

            return;
        }
        if (!file_exists($path)) {
            $this->addEmptyFile($path);

            return;
        }
        $this->getEmitter()->emit(new BuilderAddEvent($path, false, false));
        $this->phar->addFile($path, $this->absoluteToRelative($path, $this->projectPath));
    }

    public function addDirectory(string $path): void
    {
        $this->getEmitter()->emit(new BuilderAddEvent($path, true, false));
        $iterator = new PathIterator($path, $this->visitor);
        $this->phar->buildFromIterator(
            new RecursiveIteratorIterator($iterator),
            $this->projectPath
        );

        $fake = $iterator->getPathToFake();

        foreach ($fake as $subPath) {
            $this->addEmptyFile($subPath);
        }
    }

    public function addEmptyFile(string $path): void
    {
        $this->getEmitter()->emit(new BuilderAddEvent($path, false, true));
        $this->addReplacementFile($path, '');
    }

    public function addReplacementFile(string $path, string $content): void
    {
        $this->phar->addFromString($path, $content);
    }

    private function absoluteToRelative(string $path, string $ref): string
    {
        if (strpos($path, $ref) === 0) {
            return substr($path, \strlen(rtrim($ref, '\\/')) + 1);
        }

        return $path;
    }

    public function addEmpty(string $path): void
    {
        $path = str_replace('/./', '/', $path);

        if (is_dir($path)) {
            $this->addEmptyFilesForDirectory($path);

            return;
        }
        $this->getEmitter()->emit(new BuilderAddEvent($path, false, true));
        $this->addEmptyFile($path);
    }

    /** @SuppressWarnings(PHPMD.UnusedLocalVariable) */
    public function addEmptyFilesForDirectory(string $directory): void
    {
        $this->getEmitter()->emit(new BuilderAddEvent($directory, true, true));
        $visitor = new class implements PathVisitor
        {
            public function isAccepted(string $path): int
            {
                return PathVisitor::FAKE_CONTENT;
            }
        };

        $iterator = new PathIterator($directory, $visitor);
        $this->phar->buildFromIterator($iterator);

        $fake = $iterator->getPathToFake();

        foreach ($fake as $subPath) {
            $this->addEmptyFile($subPath);
        }
    }
}
