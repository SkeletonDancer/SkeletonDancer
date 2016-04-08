<?php

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
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class GushConfigGenerator implements Generator
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
        if (!$this->filesystem->exists('.git/config')) {
            return -1;
        }

        $this->filesystem->dumpFile(
            '.gush.yml',
            $this->twig->render(
                'gush.yml.twig',
                [
                    'name' => $configuration['name'],
                    'author' => [
                        'name' => $configuration['author_name'],
                        'email' => $configuration['author_email'],
                    ],
                    'license' => $configuration['license'],
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class];
    }
}
