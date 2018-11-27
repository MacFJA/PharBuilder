<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder;

use FilesystemIterator;

class PathIterator extends \RecursiveFilterIterator
{

    /** @var PathVisitor PathVisitor */
    private $visitor;
    private $toFake = [];

    public function __construct(string $directory, PathVisitor $visitor)
    {
        parent::__construct(new \RecursiveDirectoryIterator(
            $directory,
            FilesystemIterator::KEY_AS_PATHNAME
            | FilesystemIterator::CURRENT_AS_FILEINFO
            | FilesystemIterator::SKIP_DOTS
            | FilesystemIterator::UNIX_PATHS
        ));
        $this->visitor = $visitor;
    }

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @link  https://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     * @since 5.1.0
     */
    public function accept()
    {
        $path = $this->getInnerIterator()->key();
        $result = $this->visitor->isAccepted($path);

        if ($result === PathVisitor::PATH_ACCEPTED) {
            return true;
        }
        if ($result === PathVisitor::FAKE_CONTENT) {
            $this->toFake[] = $path;
        }

        return false;
    }

    public function getChildren()
    {
        /** @var \SplFileInfo $fileInfo */
        $fileInfo = $this->current();

        return new static($fileInfo->getPathname(), $this->visitor);
    }

    public function getPathToFake(): array
    {
        return $this->toFake;
    }
}
