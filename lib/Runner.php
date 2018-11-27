<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder;

use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;
use MacFJA\PharBuilder\Builder\ArchiveBuilder;
use MacFJA\PharBuilder\Builder\ArchiveCompressor;
use MacFJA\PharBuilder\Builder\Compressor\CompressorInterface;
use MacFJA\PharBuilder\Builder\Event\BuilderEvent;
use MacFJA\PharBuilder\Builder\Event\CompressorEvent;
use MacFJA\PharBuilder\Builder\Event\StatsEvent;
use MacFJA\PharBuilder\Builder\PathVisitor;
use MacFJA\PharBuilder\Builder\Stats;
use MacFJA\PharBuilder\Composer\ComposerJson;
use MacFJA\PharBuilder\Options\OptionsInterface;
use StringTemplate\Engine;

class Runner implements EmitterAwareInterface
{
    use EmitterAwareTrait;

    /** @var OptionsInterface */
    private $options;
    /** @var CompressorInterface */
    private $compressor;
    /** @var ComposerJson */
    private $composer;
    private $toFake = [];

    /**
     * Runner constructor.
     *
     * @param OptionsInterface    $options
     * @param CompressorInterface $compressor
     * @param ComposerJson        $composer
     */
    public function __construct(OptionsInterface $options, CompressorInterface $compressor, ComposerJson $composer)
    {
        $this->options = $options;
        $this->compressor = $compressor;
        $this->composer = $composer;
    }


    /**
     * @return \Phar
     * @throws \UnexpectedValueException
     * @throws \BadMethodCallException
     */
    public function execute(): \Phar
    {
        $stats = new Stats();
        $stats->start();
        $this->getEmitter()->emit('execution.before');

        $outputFilename = $this->options->getOutputPath() . DIRECTORY_SEPARATOR . $this->options->getName() . '.phar';

        $stats->setFinalPath($outputFilename);

        if (file_exists($outputFilename)) {
            unlink($outputFilename);
        }
        $phar = new \Phar(
            $outputFilename,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME,
            $this->options->getName()
        );

        $stubFile = $this->getStubFile();

        $builder = new ArchiveBuilder($phar, $this->getPathVisitor(), \dirname($this->composer->getPath()));
        $builder->setEmitter($this->getEmitter());

        $phar->setStub($stubFile);

        foreach ($this->getComposerDirectories() as $directory) {
            $builder->add($directory);
        }

        foreach ($this->options->getIncluded() as $directory) {
            $builder->add($directory);
        }

        foreach ($this->toFake as $filePath) {
            $builder->addEmpty($filePath);
        }

        // Add entry-point
        $entryPointPath = $this->options->getEntryPoint();
        $builder->addReplacementFile($entryPointPath, $this->stripShebang($entryPointPath));
        // Composer json/lock
        $builder->add($this->composer->getPath());
        $builder->add($this->composer->getComposerLock()->getPath());
        $builder->add($this->composer->getVendorDir() . '/composer');
        $builder->add($this->composer->getVendorDir() . '/autoload.php');

        $this->getEmitter()->emit(new BuilderEvent('builder.after', $builder));

        $compressor = new ArchiveCompressor($phar, $this->compressor, $this->options->getCompression());
        $this->getEmitter()->emit(new CompressorEvent('compressor.before', $compressor));
        $compressor->compress();

        $stats->end();
        $this->getEmitter()->emit(new StatsEvent('execution.after', $stats));

        return $phar;
    }

    private function getStubFile(): string
    {
        $internalName = $this->options->getName();
        $entry = $this->options->getEntryPoint();

        $templateEngine = new Engine();

        $path = $this->options->getStubPath();

        $data = file_get_contents($path);
        if ($data === false) {
            throw new \RuntimeException('Unable to read the Phar stub file');
        }

        return $templateEngine->render($data, ['alias' => $internalName, 'entry-point' => $entry]);
    }

    private function getPathVisitor()
    {
        $excluded = $this->options->getExcluded();

        return new class($excluded) implements PathVisitor
        {
            private $excluded;

            /**
             *  constructor.
             *
             * @param $excluded
             */
            public function __construct(array $excluded)
            {
                $this->excluded = $excluded;
            }

            public function isAccepted(string $path): int
            {
                foreach ($this->excluded as $needle) {
                    $wildcardNeedle = $needle . '*';
                    if (strpos($wildcardNeedle, '/') !== 0 && strpos($wildcardNeedle, '*') !== 0) {
                        $wildcardNeedle = '*' . $wildcardNeedle;
                    }

                    if (fnmatch($wildcardNeedle, $path)) {
                        return PathVisitor::PATH_REJECTED;
                    }
                }

                return PathVisitor::PATH_ACCEPTED;
            }
        };
    }

    private function getComposerDirectories(): array
    {
        $lock = $this->composer->getComposerLock();

        $path = $lock->getRequirePath();
        if ($this->options->includeDev()) {
            $path = array_merge($path, $lock->getRequireDevPath());
        } else {
            $this->toFake = array_merge($this->toFake, $lock->getRequireDevFilesAutoload());
        }

        return $path;
    }

    private function stripShebang(string $path): string
    {
        $content = file_get_contents($path);

        $shebang = "~^#!/(.*)\n~";

        return preg_replace($shebang, '', $content);
    }
}
