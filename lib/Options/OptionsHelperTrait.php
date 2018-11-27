<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Options;

trait OptionsHelperTrait
{
    private function getOnePathData($data, string $root, bool $allowFiles, bool $allowDirs): ?string
    {
        if ($data === null) {
            return null;
        }

        if (!\is_array($data)) {
            $data = [$data];
        }

        $result = $this->existingPaths($data, $root, $allowFiles, $allowDirs);

        if ($result === null || \count($result) !== 1) {
            return null;
        }

        return reset($result);
    }

    private function existingPaths(array $path, string $root, bool $allowFiles = true, bool $allowDirs = true): ?array
    {
        $values = array_filter($path, function (string $path) use ($root, $allowDirs, $allowFiles): bool {
            if ($path === '') {
                return false;
            }
            $fullPath = $root . '/' . ltrim($path, '\\/');
            $exists = file_exists($fullPath);

            if (!$exists) {
                return false;
            }

            return ($allowDirs && is_dir($fullPath)) || ($allowFiles && is_file($fullPath));
        });

        return \count($values) === 0 ? null : array_values($values);
    }

    private function getPathsData($data, string $root): ?array
    {
        if ($data === null || !\is_array($data) || \count($data) === 0) {
            return null;
        }

        return $this->existingPaths($data, $root);
    }
}
