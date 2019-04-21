<?php
/* Copyright (C) 2019 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Composer;

trait ComposerAutoloaderTrait
{
    /**
     * @param array $autoloads
     *
     * @return string[]
     */
    protected function getPsrPath(array $autoloads): array
    {
        $allPath = [];

        foreach ($autoloads as $name => $autoload) {
            $prefix = $name;

            // PSR-0 and PSR-4 are the same if the namespace doesn't matter
            // And for us, it's the case
            $psrPath = array_merge($autoload['psr-4'] ?? [], $autoload['psr-0'] ?? []);
            // Only need the path (remove the namespace)
            $psrPath = array_values($psrPath);
            // Flatten path, as you can specify multiple path for one namespace
            $psrPath = array_reduce(
                $psrPath,
                /**
                 * @param string[]        $carry
                 * @param string[]|string $item
                 *
                 * @return string[]
                 */
                function ($carry, $item): array {
                    if (\is_array($item)) {
                        return array_merge($carry, $item);
                    }
                    $carry[] = $item;

                    return $carry;
                },
                []
            );
            if (!is_int($prefix)) {
                // Add the package name (<=> path in vendor dir)
                $psrPath = array_map(function (string $path) use ($prefix): string {
                    return $prefix . DIRECTORY_SEPARATOR . $path;
                }, $psrPath);
            }

            $allPath = array_merge($allPath, $psrPath);
        }

        return $allPath;
    }

    /**
     * @param array $autoloads
     * @param bool  $withClassmap
     *
     * @return string[]
     */
    protected function getFilesPath(array $autoloads, bool $withClassmap): array
    {
        $allPath = [];

        foreach ($autoloads as $name => $autoload) {
            $prefix = $name;

            $filePath = $autoload['files'] ?? [];
            // "files" and "classmap" have a very similar behavior
            if ($withClassmap) {
                $filePath = array_merge($filePath, $autoload['classmap'] ?? []);
            }
            // Flatten path, as you can specify multiple path for one namespace
            $filePath = array_reduce(
                $filePath,
                /**
                 * @param string[]        $carry
                 * @param string[]|string $item
                 *
                 * @return string[]
                 */
                function (array $carry, $item): array {
                    if (\is_array($item)) {
                        return array_merge($carry, $item);
                    }
                    $carry[] = $item;

                    return $carry;
                },
                []
            );
            if (!is_int($prefix)) {
                // Add the package name (<=> path in vendor dir)
                $filePath = array_map(function (string $path) use ($prefix): string {
                    return $prefix . DIRECTORY_SEPARATOR . $path;
                }, $filePath);
            }
            $allPath = array_merge($allPath, $filePath);
        }

        return $allPath;
    }
}
