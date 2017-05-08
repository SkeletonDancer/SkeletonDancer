<?php

declare(strict_types=1);

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SkeletonDancer\Cli;

use SkeletonDancer\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Webmozart\Console\Adapter\ArgsInput;
use Webmozart\Console\Adapter\IOOutput;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreHandleEvent;
use Webmozart\Console\Config\DefaultApplicationConfig;

final class DancerApplicationConfig extends DefaultApplicationConfig
{
    /**
     * The version of the Application.
     */
    const VERSION = '@package_version@';

    /**
     * @var Container
     */
    private $container;

    /**
     * Creates the configuration.
     *
     * @param Container $container The service container (only to be injected during tests)
     */
    public function __construct(Container $container = null)
    {
        if (null === $container) {
            $parameters = [];
            $parameters['current_dir'] = getcwd().DIRECTORY_SEPARATOR;
            $parameters['dancers_directory'] = getenv('SKELETONDANCER_HOME') ?: getenv('HOME').'/.skeleton_dancer';

            if (!is_dir($parameters['dancers_directory'])) {
                mkdir($parameters['dancers_directory']);
            }

            $container = new Container($parameters);
        }

        $this->container = $container;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setEventDispatcher(new EventDispatcher());

        parent::configure();

        $this
            ->setName('SkeletonDancer')
            ->setDisplayName('SkeletonDancer')
            ->setVersion(self::VERSION)
            ->setDebug((bool) getenv('SKELETON_DANCER_DEBUG'))
        ;

        $this->addEventListener(
            ConsoleEvents::PRE_HANDLE,
            function (PreHandleEvent $event) {
                // Set-up the IO for the Symfony Helper classes.
                if (!isset($this->container['console.io'])) {
                    $io = $event->getIO();
                    $args = $event->getArgs();

                    $input = new ArgsInput($args->getRawArgs(), $args);
                    $input->setInteractive($io->isInteractive());

                    $this->container['console.io'] = $io;
                    $this->container['console.args'] = $args;
                    $this->container['sf.console_input'] = $input;
                    $this->container['sf.console_output'] = new IOOutput($io);
                }
            }
        );

        $this
            ->beginCommand('dance')
                ->setDescription('Generates a new skeleton structure in the current directory')
                ->addArgument('name', Argument::OPTIONAL, 'The name of the dance')
                ->addOption('import', null, Option::OPTIONAL_VALUE, 'Answers file to use instead of asking (values must be normalized)')
                ->addOption('all', null, Option::BOOLEAN, 'Ask all questions (including optional)')
                ->addOption('dry-run', null, Option::BOOLEAN, 'Show what would have been executed, without actually executing')
                ->addOption('force-overwrite', null, Option::BOOLEAN, 'Overwrite existing files (instead of aborting)')
                ->setHandler($this->container['command.dance'])
            ->end()

            ->beginCommand('install')
                ->setDescription('Installs a skeleton to your local system')
                ->addArgument('name', Argument::REQUIRED | Argument::MULTI_VALUED, 'The name of the dance')
                ->setHandler($this->container['command.install'])
            ->end()

            ->beginCommand('list')
                ->setDescription('Shows a list of all the installed dances')
                ->setHandler($this->container['command.dances'])
                ->setHandlerMethod('handleList')
            ->end()

            ->beginCommand('show')
                ->setDescription('Displays useful information about an installed dance')
                ->addArgument('dance', Argument::OPTIONAL)
                ->addOption('check-updates', null, Option::BOOLEAN, 'Check if there are new versions available')
                ->setHandler($this->container['command.dances'])
                ->setHandlerMethod('handleShow')
            ->end()
        ;
    }
}
