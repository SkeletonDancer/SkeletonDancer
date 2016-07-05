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

final class TravisConfigGenerator implements Generator
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
            return;
        }

        $this->filesystem->dumpFile(
            '.travis.yml',
            $this->twig->render(
                'travis.yml.twig',
                [
                    'phpMin' => $configuration['php_min'],
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class];
    }
}
