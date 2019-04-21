<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Compressor;

/**
 * Class SomeFilesCompression
 *
 * @codeCoverageIgnore Impossible to UT: To many dependencies to the platform
 *
 * @package            MacFJA\PharBuilder\Builder\Compressor
 */
class SomeFilesCompression extends AbstractFilesCompression
{
    /** @var CompressionVisitor */
    private $visitor;

    /**
     * SomeFilesCompression constructor.
     *
     * @param CompressionVisitor $visitor
     */
    public function __construct(CompressionVisitor $visitor)
    {
        $this->visitor = $visitor;
    }

    public static function createAuthorizeExtensions(array $extensions): SomeFilesCompression
    {
        return new SomeFilesCompression(self::createVisitor($extensions, true));
    }

    private static function createVisitor(array $extensions, bool $authorize): CompressionVisitor
    {
        return new class($extensions, $authorize) implements CompressionVisitor
        {
            /** @var string[] */
            private $extensions;
            /** @var bool */
            private $authorize;

            /**
             *  constructor.
             *
             * @param array $extensions
             * @param bool  $authorize
             */
            public function __construct(array $extensions, bool $authorize)
            {
                $this->extensions = $extensions;
                $this->authorize = $authorize;
            }

            public function isAccepted(string $path): bool
            {
                if ($this->authorize) {
                    return $this->isExtensionInList($path);
                }

                return !$this->isExtensionInList($path);
            }

            private function isExtensionInList(string $path): bool
            {
                foreach ($this->extensions as $extension) {
                    if (substr($path, -\strlen($extension)) === $extension) {
                        return true;
                    }
                }

                return false;
            }
        };
    }

    public static function createRefuseExtensions(array $extensions): SomeFilesCompression
    {
        return new SomeFilesCompression(self::createVisitor($extensions, false));
    }

    protected function compress(\PharFileInfo $pharFileInfo, int $compression): void
    {
        if ($this->visitor->isAccepted($pharFileInfo->getFilename())) {
            $pharFileInfo->compress($compression);
        }
    }
}
