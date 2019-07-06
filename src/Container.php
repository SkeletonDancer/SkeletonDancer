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

use SkeletonDancer\Autoloading\AutoloadingSetup;
use SkeletonDancer\Autoloading\Psr4ClassLoader;
use SkeletonDancer\Cli\Handler\DanceCommandHandler;
use SkeletonDancer\Cli\Handler\DancesCommandHandler;
use SkeletonDancer\Cli\Handler\InstallCommandHandler;
use SkeletonDancer\Configuration\DanceSelector;
use SkeletonDancer\Configuration\Loader;
use SkeletonDancer\Hosting\GitHubHosting;
use SkeletonDancer\Service\TwigTemplating;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;

class Container extends \Pimple\Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['.dances'] = function (self $container) {
            $args = $container['console.args'];
            if ($args->isOptionDefined('local') && $args->getOption('local')) {
                return new LocalDances(getcwd(), $container['console.io'], new Loader());
            }

            return new Dances($container['dancers_directory'], $container['console.io'], new Loader());
        };

        $this['.dance_selector'] = function (self $container) {
            return new DanceSelector($container['.dances'], $container['style'], $container);
        };

        $this['.psr4_class_loader'] = function () {
            $classLoader = new Psr4ClassLoader();
            $classLoader->register();

            return $classLoader;
        };

        $this['.autoloading_setup'] = function ($container) {
            return new AutoloadingSetup($container['.psr4_class_loader'], $container);
        };

        $this['.hosting'] = function () {
            return new GitHubHosting();
        };

        $this['.installer'] = function ($container) {
            return new Installer(
                $container['.hosting'],
                $container['process'],
                $container['.dances'],
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
            return (new TwigTemplating())->create($container['dance']);
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

        // CommandHandlers; use closure for lazy loading.

        $this['command.dance'] = $this->protect(
            function () {
                return new DanceCommandHandler(
                    $this['style'],
                    $this['filesystem'],
                    $this['.dance_selector'],
                    $this['class_initializer'],
                    $this['.autoloading_setup']
                );
            }
        );

        $this['command.dances'] = $this->protect(
            function () {
                return new DancesCommandHandler($this['style'], $this['.dance_selector'], $this['.dances']);
            }
        );

        $this['command.install'] = $this->protect(
            function () {
                return new InstallCommandHandler($this['style'], $this['.hosting'], $this['.installer']);
            }
        );
    }
}
