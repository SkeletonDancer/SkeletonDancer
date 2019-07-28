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

namespace SkeletonDancer;

use SkeletonDancer\Cli\Handler\DanceCommandHandler;
use SkeletonDancer\Cli\Handler\DancesCommandHandler;
use SkeletonDancer\Cli\Handler\InstallCommandHandler;
use SkeletonDancer\Configuration\DancesProvider;
use SkeletonDancer\Configuration\Loader;
use SkeletonDancer\Hosting\GitHubHosting;
use SkeletonDancer\Service\TwigTemplating;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;

class Container extends \Pimple\Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['.dances_provider'] = function (self $container) {
            return new DancesProvider(getcwd(), $container['dancers_directory'], new Loader(), $container['logger']);
        };

        $this['.hosting'] = function () {
            return new GitHubHosting();
        };

        $this['.installer'] = function (self $container) {
            return new Installer(
                $container['.hosting'],
                $container['process'],
                $container['.dances_provider'],
                $container['dancers_directory'],
                new Loader()
            );
        };

        $this['style'] = function (self $container) {
            return new SymfonyStyle($container['sf.console_input'], $container['sf.console_output']);
        };

        $this['class_initializer'] = function ($container) {
            return new ClassInitializer($container);
        };

        // Services for configurators and generators

        $this['twig'] = function (self $container) {
            return new TwigTemplating();
        };

        $this['process'] = function (self $container) {
            return new Service\CliProcess($container['sf.console_output']);
        };

        $this['git'] = function (self $container) {
            return new Service\Git($container['process']);
        };

        $this['filesystem'] = function (self $container) {
            return new Service\Filesystem(
                new SfFilesystem(),
                $container['current_dir'],
                $container['console.args']->getOption('force-overwrite')
            );
        };

        $this['logger'] = function (self $container) {
            return new ConsoleLogger($container['sf.console_output']);
        };

        // CommandHandlers; use closure for lazy loading.

        $this['command.dance'] = $this->protect(
            function () {
                return new DanceCommandHandler(
                    $this['style'],
                    $this['filesystem'],
                    $this['.dances_provider'],
                    $this['class_initializer']
                );
            }
        );

        $this['command.dances'] = $this->protect(
            function () {
                return new DancesCommandHandler($this['style'], $this['.dances_provider']);
            }
        );

        $this['command.install'] = $this->protect(
            function () {
                return new InstallCommandHandler($this['style'], $this['.hosting'], $this['.installer']);
            }
        );
    }
}
