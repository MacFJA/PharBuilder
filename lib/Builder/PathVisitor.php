<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Builder;

interface PathVisitor
{
    public const PATH_REJECTED = 0; // 00
    public const PATH_ACCEPTED = 1; // 01
    public const FAKE_CONTENT = 2;  // 10

    public function isAccepted(string $path): int;
}
