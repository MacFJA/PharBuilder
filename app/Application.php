<?php


namespace MacFJA\PharBuilder;

use League\Event\EventInterface;
use MacFJA\PharBuilder\Commands\Build;
use MacFJA\PharBuilder\Commands\Package;
use MacFJA\PharBuilder\Event\EventManager;
use MacFJA\Symfony\Console\Filechooser\FilechooserHelper;
use Symfony\Component\Console\Application as Base;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Application.
 * Expose commands, the main purpose of this class is to get all information to work.
 *
 * Exit code of the application are:
 *  - `0`: Success
 *  - `1`: Wrong *composer.json* file
 *  - `2`: Wrong entry point for the application (file does not exist)
 *  - `3`: Wrong output directory
 *  - `4`: The Phar filename is not valid
 *  - `5`: PHP ini wrongly setting up
 *  - `6`: Require input, but in no-interactive mode
 *
 * @package MacFJA\PharBuilder
 * @author  MacFJA
 * @license MIT
 */
class Application extends Base
{
    /**
     * The phar builder
     *
     * @var PharBuilder
     */
    protected $builder;
    /**
     * The application event manager
     *
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Get the application PHAR Builder
     *
     * @return PharBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }
    /**
     * Init the application and create the main and default command
     *
     * @throws LogicException
     */
    public function __construct()
    {
        parent::__construct('MacFJA PharBuilder', '@dev');

        $this->eventManager = new EventManager($this);

        $this->getHelperSet()->set(new FilechooserHelper());

        // Add the full interactive command
        $this->add(new Build());
        // Add the Composer based command
        $this->add(new Package());

        /*
         * Set the build command the default command.
         * So without argument, instead of the help command, the build command will be launch
         */
        $this->setDefaultCommand('build');

        $this->eventManager->registerApplication();
    }

    /**
     * Emit an event.
     *
     * @param EventInterface $event The event to send
     *
     * @return EventInterface
     */
    public function emit($event)
    {
        $this->eventManager->emit($event);
    }

    /**
     * Check if the application can run properly in the current environment.
     * Exit code: `5`
     *
     * @param SymfonyStyle $ioStyle The Symfony Style Input/Output
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression, PHPMD.Superglobals) -- Normal/Wanted behavior
     */
    public function checkSystem(SymfonyStyle $ioStyle)
    {
        $pharReadonly = ini_get('phar.readonly');
        if ($pharReadonly === '1') {
            $ioStyle->error(array(
                'The configuration of your PHP do not authorize PHAR creation. (see phar.readonly in you php.ini)',
                'Alternatively you can run:' . PHP_EOL .
                'php -d phar.readonly=0 ' . implode(' ', $_SERVER['argv'])
            ));
            exit(5);
        }
        switch ($this->eventManager->isSignalCompatible()) {
            case EventManager::SIGNAL_COMP_COMPILATION:
                $ioStyle->warning(
                    array(
                        'Your version on PHP is not compiled with the flag "--enable-pcntl".',
                        'Unix interruption will not work'
                    )
                );
                break;
            case EventManager::SIGNAL_COMP_WINDOWS:
                $ioStyle->warning(
                    'Your system don\'t support Unix signal. Unix interruption will not work'
                );
                break;
        }
    }

    /**
     * Configure Output interface and do some checks
     *
     * @param InputInterface  $input  The CLI input interface (reading user input)
     * @param OutputInterface $output The CLI output interface (display message)
     *
     * @return void
     *///@codingStandardsIgnoreLine
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);

        $ioStyle       = new SymfonyStyle($input, $output);
        $this->builder = new PharBuilder($ioStyle);
        foreach ($this->all() as $command) {
            if ($command instanceof Commands\Base) {
                $command->setIo($ioStyle);
            }
        }

        $this->checkSystem($ioStyle);
    }
}
