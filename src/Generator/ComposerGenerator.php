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

use Rollerworks\Tools\SkeletonDancer\Configurator\ComposerConfigurator;
use Rollerworks\Tools\SkeletonDancer\Configurator\GeneralConfigurator;
use Rollerworks\Tools\SkeletonDancer\Configurator\LicenseConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Composer;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class ComposerGenerator implements Generator
{
    private $filesystem;
    private $composer;

    public function __construct(Filesystem $filesystem, Composer $composer)
    {
        $this->filesystem = $filesystem;
        $this->composer = $composer;
    }

    public function generate(array $configuration)
    {
        $configuration['composer'] = array_merge(
            [
                'name' => $configuration['package_name'],
                'description' => '',
                'homepage' => '',
                'type' => 'library',
                'license' => $configuration['license'],
                'authors' => [
                    [
                        'name' => $configuration['author_name'],
                        'email' => $configuration['author_email'],
                    ],
                ],
                'require' => [],
                'require-dev' => [],
                'autoload' => [],
            ],
            $configuration['composer'] ?? []
        );

        if ('' === $configuration['composer']['homepage']) {
            unset($configuration['composer']['homepage']);
        }

        if (empty($configuration['composer']['autoload'])) {
            $configuration['composer']['autoload'] = [
                'psr-4' => [
                    $configuration['namespace'].'\\' => $configuration['src_dir'],
                ],
            ];
        }

        $composer = $configuration['composer'];
        $composer['require'] = [
            'php' => '^'.$configuration['php_min'],
        ];

        // XXX Remove when empty (same goes for other values)
        $composer['require-dev'] = [];

        // Add extra newline to content to fix content mismatch when dumping
        $this->filesystem->dumpFile(
            'composer.json',
            preg_replace(
                '/"require(-dev)?": \[\]/',
                '"require$1": { }',
                json_encode($composer, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES)."\n"
            )
        );

        $this->composer->requirePackage($configuration['composer']['require']);
        $this->composer->requireDevPackage($configuration['composer']['require-dev']);
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class, LicenseConfigurator::class, ComposerConfigurator::class];
    }
}
