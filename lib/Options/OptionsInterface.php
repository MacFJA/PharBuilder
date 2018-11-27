<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

interface OptionsInterface
{
    public function getOutputPath(): ?string;

    public function getName(): ?string;

    public function includeDev(): ?bool;

    public function getStubPath(): ?string;

    public function getCompression(): ?int;

    public function getEntryPoint(): ?string;

    public function getIncluded(): ?array;

    public function getExcluded(): array;
}
