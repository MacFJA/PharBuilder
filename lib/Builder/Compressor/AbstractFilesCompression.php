<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Compressor;

/**
 * Class AbstractFilesCompression
 *
 * @codeCoverageIgnore Impossible to UT: To many dependencies to the platform
 *
 * @package            MacFJA\PharBuilder\Builder\Compressor
 */
abstract class AbstractFilesCompression implements CompressorInterface
{
    use CompressionValidatorTrait;

    public function execute(\Phar $phar, int $compression): bool
    {
        if (!$this->isCompressionValid($compression)) {
            return false;
        }

        $content = $phar->getChildren();

        /** @var \PharFileInfo $pharFileInfo */
        foreach ($content as $pharFileInfo) {
            if ($pharFileInfo->isDir()) {
                continue;
            }
            $this->compress($pharFileInfo, $compression);
        }

        return true;
    }

    abstract protected function compress(\PharFileInfo $pharFileInfo, int $compression): void;
}
