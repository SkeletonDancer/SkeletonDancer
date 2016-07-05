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

final class ReadMeGenerator implements Generator
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
            'README.md',
            $this->twig->render(
                'readme.md.twig',
                [
                    'name' => $configuration['name'],
                    'packageName' => $configuration['package_name'],
                    'phpMin' => $configuration['php_min'],
                    'authorName' => $configuration['author_name'],
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class];
    }
}
