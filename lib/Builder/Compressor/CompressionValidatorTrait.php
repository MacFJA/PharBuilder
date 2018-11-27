<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Compressor;

trait CompressionValidatorTrait
{
    private function isCompressionValid(int $compression): bool
    {
        if (!\in_array($compression, [\Phar::BZ2, \Phar::GZ], true)) {
            return false;
        }

        return \Phar::canCompress($compression);
    }
}
