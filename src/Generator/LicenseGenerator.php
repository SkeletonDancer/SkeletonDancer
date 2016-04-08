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
use Rollerworks\Tools\SkeletonDancer\Configurator\LicenseConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class LicenseGenerator implements Generator
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
            'LICENSE',
            $this->twig->render(
                'Licenses/'.strtolower($configuration['license']).'.txt.twig',
                [
                    'productName' => $configuration['name'],
                    'author' => [
                        'name' => $configuration['author_name'],
                        'email' => $configuration['author_email'],
                    ],
                    '_block' => 'file',
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class, LicenseConfigurator::class];
    }
}
