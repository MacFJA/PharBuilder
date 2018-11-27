<?php
/* Copyright (C) 2018 MacFJA
 *
 * This software may be modified and distributed under the terms
 * of the MIT license. See the LICENSE.md file for details.
 */

namespace MacFJA\PharBuilder\Command;

use League\Event\EmitterAwareInterface;
use League\Event\EmitterAwareTrait;
use MacFJA\PharBuilder\Builder\Compressor\AllFilesCompression;
use MacFJA\PharBuilder\Composer\ComposerJson;
use MacFJA\PharBuilder\Options\ConsoleOptions;
use MacFJA\PharBuilder\Options\OptionsChain;
use MacFJA\PharBuilder\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Package extends Command implements EmitterAwareInterface
{
    use EmitterAwareTrait;

    /**
     * @codeCoverageIgnore
     */
    protected function configure()
    {
        $definition = ConsoleOptions::getInputDefinition();
        $definition->addArgument(new InputArgument(
            'composer-json',
            InputArgument::OPTIONAL,
            'The path to the <info>composer.json</info> file.' . "\n" . 'If the argument is not defined, search of a <info>composer.json</info> inside the current directory'
        ));
        $this->setDefinition($definition);
        $this->setDescription('Generate a Phar from a <info>composer.json</info>');
        $this->setHelp(
            <<<HELP

HELP
        );
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerJsonPath = $input->getArgument('composer-json');
        if ($composerJsonPath === null) {
            $composerJsonPath = rtrim(getcwd(), '\\/') . '/composer.json';
        }
        $composer = new ComposerJson($composerJsonPath);

        if (!$composer->isValid()) {
            throw new \InvalidArgumentException('The file "' . $composer->getPath() . '" is not a valid *composer.json* file');
        }

        $options = OptionsChain::createDefaultChain($this->getEmitter(), $input, $composer);
        $runner = new Runner($options, new AllFilesCompression(), $composer);
        $runner->setEmitter($this->getEmitter());
        $runner->execute();
    }
}
