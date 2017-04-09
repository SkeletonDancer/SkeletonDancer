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

namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Rollerworks\Tools\SkeletonDancer\Configurator\GeneralConfigurator;
use Rollerworks\Tools\SkeletonDancer\Configurator\PhpCsConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class PhpCsGenerator implements Generator
{
    private $filesystem;
    private $twig;

    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    public function generate(array $configuration)
    {
        $this->filesystem->dumpFile(
            '.php_cs',
            $this->twig->render(
                'php_cs.php.twig',
                [
                    'name' => $configuration['name'],
                    'author' => [
                        'name' => $configuration['author_name'],
                        'email' => $configuration['author_email'],
                    ],
                    'license' => $configuration['license'] ?? null,
                    'level' => $configuration['php_cs_level'],
                    'version_1_bc' => $configuration['php_cs_version_1_bc'],
                    'linting' => $configuration['php_cs_linting'],
                    'fixers' => $configuration['php_cs_enabled_fixers'],
                    'fixers_v1' => $configuration['php_cs_enabled_fixers_v1'],
                    'disabled_fixers' => $configuration['php_cs_disabled_fixers'],
                    'disabled_fixers_v1' => $configuration['php_cs_disabled_fixers_v1'],
                    'styleci_bridge' => $configuration['php_cs_styleci_bridge'],
                    'finder' => $configuration['php_cs_finder'],
                ]
            )
        );

        if ($configuration['styleci_enabled']) {
            $this->filesystem->dumpFile(
                '.styleci.yml',
                $this->twig->render(
                    'styleci.yml.twig',
                    [
                        'name' => $configuration['name'],
                        'author' => [
                            'name' => $configuration['author_name'],
                            'email' => $configuration['author_email'],
                        ],
                        'license' => $configuration['license'] ?? null,
                        'level' => $configuration['php_cs_level'],
                        'linting' => $configuration['php_cs_linting'],
                        'fixers' => $configuration['php_cs_enabled_fixers'],
                        'disabled_fixers' => $configuration['php_cs_disabled_fixers'],
                        'finder' => $configuration['php_cs_finder'],
                    ]
                )
            );
        }
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class, PhpCsConfigurator::class];
    }
}
