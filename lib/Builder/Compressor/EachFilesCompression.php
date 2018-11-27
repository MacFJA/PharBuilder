<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder\Compressor;

/**
 * Class EachFilesCompression
 *
 * @codeCoverageIgnore Impossible to UT: To many dependencies to the platform
 *
 * @package            MacFJA\PharBuilder\Builder\Compressor
 */
class EachFilesCompression extends AbstractFilesCompression
{
    protected function compress(\PharFileInfo $pharFileInfo, int $compression): void
    {
        $pharFileInfo->compress($compression);
    }
}
