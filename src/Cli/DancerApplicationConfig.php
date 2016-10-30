<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Cli;

use Rollerworks\Tools\SkeletonDancer\Container;
use Rollerworks\Tools\SkeletonDancer\EventListener\AutoLoadingSetupListener;
use Rollerworks\Tools\SkeletonDancer\EventListener\ExpressionFunctionsProviderSetupListener;
use Rollerworks\Tools\SkeletonDancer\EventListener\ProjectDirectorySetupListener;
use Webmozart\Console\Adapter\ArgsInput;
use Webmozart\Console\Adapter\IOOutput;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Event\ConsoleEvents;
use Webmozart\Console\Api\Event\PreHandleEvent;
use Webmozart\Console\Api\Formatter\Style;
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
            $parameters['current_dir'] = getcwd().'/';
            $parameters['dancer_directory'] = null;

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
        $this->setEventDispatcher($this->container->getEventDispatcherService());

        parent::configure();

        $this
            ->setName('dancer')
            ->setDisplayName('SkeletonDancer')
            ->addOption(
                'project-directory',
                null,
                Option::OPTIONAL_VALUE,
                'The root directory of the project, this is where the ".dancer" directory is located. '.
                'When omitted the directory is automatically searched by the first occurrence of the ".dancer" directory
                in the parent directory structure, and falls back to the current working directory.'
            )
            ->addOption(
                'config-file',
                null,
                Option::OPTIONAL_VALUE | Option::NULLABLE,
                'Configuration file to load. When omitted the file `.dancer.yml` is automatically searched in the parent directory structure. Pass "null" to disable',
                ''
            )
            ->addOption('overwrite', null, Option::REQUIRED_VALUE, 'Default operation for existing files: abort, skip, force, ask, backup')

            ->setVersion(self::VERSION)
            ->setDebug('true' === getenv('SKELETON_DANCER_DEBUG'))
            ->addStyle(Style::tag('good')->fgGreen())
            ->addStyle(Style::tag('bad')->fgRed())
            ->addStyle(Style::tag('warn')->fgYellow())
            ->addStyle(Style::tag('hl')->fgGreen())
        ;

        $this->addEventListener(
            ConsoleEvents::PRE_HANDLE,
            function (PreHandleEvent $event) {
                // Set-up the IO for the Symfony Helper classes.
                if (!isset($this->container['console_io'])) {
                    $io = $event->getIO();
                    $args = $event->getArgs();

                    $input = new ArgsInput($args->getRawArgs(), $args);
                    $input->setInteractive($io->isInteractive());

                    $this->container['console_io'] = $io;
                    $this->container['console_args'] = $args;
                    $this->container['sf.console_input'] = $input;
                    $this->container['sf.console_output'] = new IOOutput($io);
                }
            }
        );
        $this->addEventListener(ConsoleEvents::PRE_HANDLE, new ProjectDirectorySetupListener($this->container));
        $this->addEventListener(ConsoleEvents::PRE_HANDLE, new AutoLoadingSetupListener($this->container));
        $this->addEventListener(ConsoleEvents::PRE_HANDLE, new ExpressionFunctionsProviderSetupListener($this->container));

        $this
            ->beginCommand('generate')
                ->setDescription('Generates a new skeleton structure in the current directory')
                ->addArgument('profile', Argument::OPTIONAL, 'The name of the profile')
                ->addOption('all', null, Option::BOOLEAN, 'Ask all questions (including optional)')
                ->setHandler(function () {
                    return new Handler\GenerateCommandHandler(
                        $this->container['style'],
                        $this->container['configurator_loader'],
                        $this->container['config'],
                        $this->container['profile_resolver'],
                        $this->container['defaults_processor']
                    );
                })
            ->end()

            ->beginCommand('profile')
                ->setDescription('Manage the profiles of your project')
                ->setHandler(function () {
                    return new Handler\ProfileCommandHandler(
                        $this->container['style'],
                        $this->container['configurator_loader'],
                        $this->container['config']
                    );
                })

                ->beginSubCommand('list')
                    ->setHandlerMethod('handleList')
                    ->markDefault()
                ->end()

                ->beginSubCommand('show')
                    ->addArgument('name', Argument::OPTIONAL, 'The name of the profile')
                    ->setHandlerMethod('handleShow')
                ->end()
            ->end()
        ;
    }
}
