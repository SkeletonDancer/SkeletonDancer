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
use Rollerworks\Tools\SkeletonDancer\ExpressionLanguage\FilesystemProvider;
use Rollerworks\Tools\SkeletonDancer\ExpressionLanguage\StringProvider;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use Symfony\Component\Yaml\Yaml;

class Container extends \Pimple\Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['dispatcher'] = function () {
            return new EventDispatcher();
        };

        $this['expression_language'] = function (Container $container) {
            $expressionLanguage = new ExpressionLanguage();
            $expressionLanguage->registerProvider(new StringProvider());
            $expressionLanguage->registerProvider(new FilesystemProvider());
            $expressionLanguage->register(
                'get_config',
                function ($name, $default) {
                    return sprintf("\$container['config']->get(%s, %s)", $name, $default);
                },
                function (array $arguments, $name, $default = null) use ($container) {
                    return $container['config']->get($name, $default);
                }
            );
            $expressionLanguage->register(
                'date',
                function ($format) {
                    return sprintf('date(%s)', $format);
                },
                function (array $arguments, $format) {
                    return date($format);
                }
            );

            return $expressionLanguage;
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

            if (!$container['console_io']->isInteractive()) {
                $config['interactive'] = false;
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

            if ($interactive && !$container['config']->get('interactive', true)) {
                $interactive = $container['console_args']->getOption('force-interactive');
            }

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

        $this['twig'] = function (Container $container) {
            $cacheDir = sys_get_temp_dir().'/twig';
            $twig = new \Twig_Environment(
                $loader = new \Twig_Loader_Filesystem(__DIR__.'/../Resources/Templates'),
                [
                    'debug' => true,
                    'cache' => new \Twig_Cache_Filesystem($cacheDir),
                    'strict_variables' => true,
                ]
            );

            if (isset($container['dancer_directory'])) {
                if (is_dir($container['dancer_directory'].'/templates')) {
                    $loader->prependPath($container['dancer_directory'].'/templates');
                }

                $profile = str_replace(':', '_', (string) $container['config']->get('active_profile'));

                if ('' !== $profile && is_dir($container['dancer_directory'].'/templates/'.$profile)) {
                    $loader->addPath($container['dancer_directory'].'/templates/'.$profile);
                }
            }

            $twig->addFunction(
                new \Twig_SimpleFunction(
                    'doc_header',
                    function ($value, $format) {
                        return $value."\n".str_repeat($format, strlen($value));
                    }
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'normalizeNamespace',
                    function ($value) {
                        return str_replace('\\\\', '\\', $value);
                    },
                    ['is_safe' => ['html', 'yml']]
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'camelize',
                    function ($value) {
                        return StringUtil::camelize($value);
                    }
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'camelize_method',
                    function ($value) {
                        return lcfirst(StringUtil::camelize($value));
                    },
                    ['is_safe' => ['all']]
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'underscore',
                    function ($value) {
                        return StringUtil::underscore($value);
                    },
                    ['is_safe' => ['all']]
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'escape_namespace',
                    'addslashes',
                    ['is_safe' => ['html', 'yml']]
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'indent_lines',
                    function ($value, $level = 1) {
                        return preg_replace("/\n/", "\n".str_repeat('    ', $level), $value);
                    },
                    ['is_safe' => ['all']]
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'comment_lines',
                    function ($value, $char = '#') {
                        return preg_replace("/\n/", "\n.$char", $value);
                    },
                    ['is_safe' => ['all']]
                )
            );

            $twig->addFilter(
                new \Twig_SimpleFilter(
                    'yaml_dump',
                    function ($value, $inline = 4, $indent = 4, $flags = 0) {
                        return Yaml::dump($value, $inline, 4, $indent, $flags);
                    },
                    ['is_safe' => ['yml', 'yaml']]
                )
            );

            return $twig;
        };

        // Services for configurators and generators

        $this['process'] = function (Container $container) {
            $helperSet = new HelperSet(
                [
                    new DebugFormatterHelper(),
                    new ProcessHelper(),
                ]
            );

            return new Service\CliProcess($helperSet->get('process'), $container['sf.console_output']);
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
     * @return EventDispatcher
     */
    public function getEventDispatcherService()
    {
        return $this['dispatcher'];
    }

    /**
     * @return ConfiguratorsLoader
     */
    public function getConfiguratorsLoaderService()
    {
        return $this['configurator_loader'];
    }
}
