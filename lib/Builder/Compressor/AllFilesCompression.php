<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Compressor;

/**
 * Class AllFilesCompression
 *
 * @codeCoverageIgnore Impossible to UT: To many dependencies to the platform
 *
 * @package            MacFJA\PharBuilder\Builder\Compressor
 */
class AllFilesCompression implements CompressorInterface
{
    use CompressionValidatorTrait;

    public function execute(\Phar $phar, int $compression): bool
    {
        if (!$this->isCompressionValid($compression)) {
            return false;
        }

        $phar->compressFiles($compression);

        return true;
    }
}
