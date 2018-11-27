<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */
namespace MacFJA\PharBuilder\tests\Composer;

use MacFJA\PharBuilder\Composer\ComposerJson;
use MacFJA\PharBuilder\Composer\ComposerLock;
use PHPUnit\Framework\TestCase;

/**
 * Class ComposerLockTest
 *
 * @covers  \MacFJA\PharBuilder\Composer\ComposerLock
 * @uses  \MacFJA\PharBuilder\Composer\ComposerJson
 * @package MacFJA\PharBuilder\tests\Composer
 */
class ComposerLockTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     * @param $composerPath
     * @param $expected
     */
    public function testGetRequireDevPath($composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $composerLock = new ComposerLock($composer);
        $this->assertEquals($expected, $composerLock->getRequireDevPath());
    }

    /**
     * @dataProvider dataProvider
     * @param $composerPath
     * @param $expected
     */
    public function testGetRequireDevFilesAutoload($composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $composerLock = new ComposerLock($composer);
        $this->assertEquals($expected, $composerLock->getRequireDevFilesAutoload());
    }

    /**
     * @dataProvider dataProvider
     * @param $composerPath
     * @param $expected
     */
    public function testGetRequirePath($composerPath, $expected)
    {
        $composer = new ComposerJson($composerPath);
        $composerLock = new ComposerLock($composer);
        $this->assertEquals($expected, $composerLock->getRequirePath());
    }

    public function dataProvider(string $methodName): array
    {
        if ($methodName === 'testGetRequireDevPath') {
            $validExpected = [
                'vendor/composer/xdebug-handler/src',
                'vendor/consolidation/annotated-command/src',
                'vendor/consolidation/config/src',
                'vendor/consolidation/log/src',
                'vendor/consolidation/output-formatters/src',
                'vendor/consolidation/robo/src',
                'vendor/consolidation/self-update/src',
                'vendor/container-interop/container-interop/src/Interop/Container/',
                'vendor/dflydev/dot-access-data/src',
                'vendor/doctrine/instantiator/src/Doctrine/Instantiator/',
                'vendor/edgedesign/phpqa/src',
                'vendor/grasmash/expander/src/',
                'vendor/grasmash/yaml-expander/src/',
                'vendor/jean85/pretty-package-versions/src/',
                'vendor/league/container/src',
                'vendor/muglug/package-versions-56/src/PackageVersions',
                'vendor/myclabs/deep-copy/src/DeepCopy/',
                'vendor/nikic/php-parser/lib/PhpParser',
                'vendor/ocramius/package-versions/src/PackageVersions',
                'vendor/openlss/lib-array2xml/',
                'vendor/pdepend/pdepend/src/main/php/PDepend',
                'vendor/phpdocumentor/reflection-common/src',
                'vendor/phpdocumentor/reflection-docblock/src/',
                'vendor/phpdocumentor/type-resolver/src/',
                'vendor/phpmd/phpmd/src/main/php',
                'vendor/phpmetrics/phpmetrics/./src/',
                'vendor/phpspec/prophecy/src/',
                'vendor/phpstan/phpdoc-parser/src/',
                'vendor/phpstan/phpstan/src/',
                'vendor/phpstan/phpstan/build/PHPStan',
                'vendor/psr/container/src/',
                'vendor/psr/log/Psr/Log/',
                'vendor/symfony/config/',
                'vendor/symfony/console/',
                'vendor/symfony/dependency-injection/',
                'vendor/symfony/event-dispatcher/',
                'vendor/symfony/filesystem/',
                'vendor/symfony/finder/',
                'vendor/symfony/polyfill-ctype/',
                'vendor/symfony/polyfill-mbstring/',
                'vendor/symfony/process/',
                'vendor/symfony/yaml/',
                'vendor/twig/twig/src/',
                'vendor/twig/twig/lib/',
                'vendor/vimeo/psalm/src/Psalm',
                'vendor/webmozart/assert/src/',
                'vendor/edgedesign/phpqa/src/report.php',
                'vendor/edgedesign/phpqa/src/paths.php',
                'vendor/jakub-onderka/php-parallel-lint/./',
                'vendor/myclabs/deep-copy/src/DeepCopy/deep_copy.php',
                'vendor/nette/bootstrap/src/',
                'vendor/nette/di/src/',
                'vendor/nette/finder/src/',
                'vendor/nette/neon/src/',
                'vendor/nette/php-generator/src/',
                'vendor/nette/robot-loader/src/',
                'vendor/nette/utils/src/loader.php',
                'vendor/nette/utils/src/',
                'vendor/phar-io/manifest/src/',
                'vendor/phar-io/version/src/',
                'vendor/php-cs-fixer/diff/src/',
                'vendor/phploc/phploc/src/',
                'vendor/phpmetrics/phpmetrics/./src/functions.php',
                'vendor/phpunit/php-code-coverage/src/',
                'vendor/phpunit/php-file-iterator/src/',
                'vendor/phpunit/php-text-template/src/',
                'vendor/phpunit/php-timer/src/',
                'vendor/phpunit/php-token-stream/src/',
                'vendor/phpunit/phpunit/src/',
                'vendor/phpunit/phpunit-mock-objects/src/',
                'vendor/sebastian/code-unit-reverse-lookup/src/',
                'vendor/sebastian/comparator/src/',
                'vendor/sebastian/diff/src/',
                'vendor/sebastian/environment/src/',
                'vendor/sebastian/exporter/src/',
                'vendor/sebastian/finder-facade/src/',
                'vendor/sebastian/global-state/src/',
                'vendor/sebastian/object-enumerator/src/',
                'vendor/sebastian/object-reflector/src/',
                'vendor/sebastian/phpcpd/src/',
                'vendor/sebastian/recursion-context/src/',
                'vendor/sebastian/resource-operations/src/',
                'vendor/sebastian/version/src/',
                'vendor/symfony/polyfill-ctype/bootstrap.php',
                'vendor/symfony/polyfill-mbstring/bootstrap.php',
                'vendor/theseer/fdomdocument/src/',
                'vendor/theseer/tokenizer/src/'
            ];

            return [
                [__DIR__ . '/../fixtures/composer.json', $validExpected],
                [__DIR__ . '/../fixtures/composer2.json', []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', []],
                [__DIR__ . '/../fixtures/composer.lock', []]
            ];
        } elseif ($methodName === 'testGetRequireDevFilesAutoload') {
            return [
                [
                    __DIR__ . '/../fixtures/composer.json',
                    [
                        'vendor/edgedesign/phpqa/src/report.php',
                        'vendor/edgedesign/phpqa/src/paths.php',
                        'vendor/myclabs/deep-copy/src/DeepCopy/deep_copy.php',
                        'vendor/nette/utils/src/loader.php',
                        'vendor/phpmetrics/phpmetrics/./src/functions.php',
                        'vendor/symfony/polyfill-ctype/bootstrap.php',
                        'vendor/symfony/polyfill-mbstring/bootstrap.php',
                    ]
                ],
                [__DIR__ . '/../fixtures/composer2.json', []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', []],
                [__DIR__ . '/../fixtures/composer.lock', []]
            ];
        } elseif ($methodName === 'testGetRequirePath') {
            return [
                [__DIR__ . '/../fixtures/composer.json',[]],
                [__DIR__ . '/../fixtures/composer2.json', []],
                [__DIR__ . '/../fixtures/error-composer/composer.json', []],
                [__DIR__ . '/../fixtures/composer.lock', []]
            ];
        }

        return [];
    }
}
