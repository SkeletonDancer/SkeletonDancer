<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer;

use Rollerworks\Tools\SkeletonDancer\Configuration\AutomaticProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\ConfigLoader;
use Rollerworks\Tools\SkeletonDancer\Configuration\DefaultsProcessor;
use Rollerworks\Tools\SkeletonDancer\Configuration\InteractiveProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\ProfilesProcessor;
use Rollerworks\Tools\SkeletonDancer\Configurator\Loader as ConfiguratorsLoader;
use Rollerworks\Tools\SkeletonDancer\ExpressionLanguage\Factory;
use Rollerworks\Tools\SkeletonDancer\Service\TwigTemplating;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;

class Container extends \Pimple\Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['config'] = function (Container $container) {
        };

        $this['expression_language'] = function (Container $container) {
            return (new Factory($container['config']))->create();
        };

        $this['config'] = function (Container $container) {
            $files = [__DIR__.'/../Resources/config/base.yml'];

            if (null !== $configFile = $container['config_file']) {
                $files[] = $configFile;
            }

            $config = (new ConfigLoader(realpath($container['dancer_directory'])))->processFiles($files);
            $config['current_dir'] = str_replace('\\', '//', realpath($container['current_dir']));
            $config['project_directory'] = str_replace('\\', '//', realpath($container['project_directory']));
            $config['current_dir_relative'] = mb_substr($config['current_dir'], strlen($config['project_directory']) + 1);
            $config['current_dir_name'] = basename($container['current_dir']);
            $config['config_file'] = $container['config_file'];
            $config['profiles'] = (new ProfilesProcessor())->process($config['profiles']);

            if (null !== $container['dancer_directory']) {
                $config['dancer_directory'] = str_replace('\\', '//', realpath($container['dancer_directory']));
            }

            if (null !== $overwrite = $container['console_args']->getOption('overwrite')) {
                $config['overwrite'] = $overwrite;
            }

            return new Config($config, ['current_dir', 'current_dir_relative', 'project_directory', 'dancer_directory', 'config_file']);
        };

        $this['defaults_processor'] = function (Container $container) {
            return new DefaultsProcessor($container['expression_language'], $container['config']);
        };

        $this['configurator_loader'] = $this->factory(
            function (Container $container) {
                return new ConfiguratorsLoader($container['class_initializer']);
            }
        );

        $this['profile_resolver'] = function (Container $container) {
            $automaticResolver = new AutomaticProfileResolver($container['config']);
            $interactive = $container['console_io']->isInteractive();
            if ($interactive) {
                return new InteractiveProfileResolver(
                    $container['config'],
                    $container['style'],
                    $container['console_io'],
                    $automaticResolver
                );
            }

            return $automaticResolver;
        };

        $this['style'] = function (Container $container) {
            return new SymfonyStyle($container['sf.console_input'], $container['sf.console_output']);
        };

        $this['class_initializer'] = function ($container) {
            return new ClassInitializer($container);
        };

        // Services for configurators and generators

        $this['twig'] = function (Container $container) {
            return (new TwigTemplating($container['config']))->create();
        };

        $this['process'] = function (Container $container) {
            return new Service\CliProcess($container['sf.console_output']);
        };

        $this['composer'] = function (Container $container) {
            return new Service\Composer($container['process']);
        };

        $this['git'] = function (Container $container) {
            return new Service\Git($container['process']);
        };

        $this['filesystem'] = function (Container $container) {
            $paths = [
                'currentDir' => $container['config']->get('current_dir'),
                'projectDir' => $container['config']->get('project_directory'),
                'dancerDir' => $container['config']->get('dancer_directory'),
            ];

            return new Service\Filesystem(
                new SfFilesystem(),
                $container['style'],
                $paths,
                $container['config']->get('overwrite', 'abort')
            );
        };
    }

    /**
     * @return ConfiguratorsLoader
     */
    public function getConfiguratorsLoaderService()
    {
        return $this['configurator_loader'];
    }
}
