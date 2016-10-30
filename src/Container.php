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

namespace Rollerworks\Tools\SkeletonDancer;

use Rollerworks\Tools\SkeletonDancer\Configuration\AnswersSetFactory;
use Rollerworks\Tools\SkeletonDancer\Configuration\ClassLoader;
use Rollerworks\Tools\SkeletonDancer\Configuration\ConfigFactory;
use Rollerworks\Tools\SkeletonDancer\Configuration\ProfileConfigResolver;
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
            return (new ConfigFactory($container['current_dir'], $container['project_directory']))
                ->setDancerDirectory($container['dancer_directory'])
                ->setConfigFile($container['config_file'])
                ->setFileOverwrite($container['console_args']->getOption('overwrite'))
                ->create();
        };

        $this['expression_language'] = function (Container $container) {
            return (new Factory($container['config']))->create();
        };

        $this['answers_set_factory'] = function (Container $container) {
            return new AnswersSetFactory($container['expression_language']);
        };

        $this['profile_config_resolver'] = function (Container $container) {
            return new ProfileConfigResolver(
                $container['config']->getProfiles(),
                new ClassLoader($container['class_initializer']),
                $container['config']->get('variables', []),
                $container['config']->get('defaults', [])
            );
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
}
