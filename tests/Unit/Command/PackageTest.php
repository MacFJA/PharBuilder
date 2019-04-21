<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\UnitTest\Command;

use MacFJA\PharBuilder\Command\Package;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class PackageTest
 *
 * @covers \MacFJA\PharBuilder\Command\Package
 * @uses \MacFJA\PharBuilder\Composer\ComposerJson
 * @uses \MacFJA\PharBuilder\Options\ConsoleOptions
 *
 * @package MacFJA\PharBuilder\tests\Command
 */
class PackageTest extends TestCase
{

    public function testExecuteOnInvalidComposerJson()
    {
        $package = new Package();

        $path = __DIR__ . '/../fixtures/error-composer/composer.json';

        $input = new ArrayInput([
            'composer-json' => $path
        ]);

        try {
            $package->run($input, new NullOutput());
            self::fail();
        } catch (\InvalidArgumentException $exception) {
            self::assertEquals('The file "' . $path . '" is not a valid *composer.json* file', $exception->getMessage());
        }
    }
}
