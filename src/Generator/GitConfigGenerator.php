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

use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class GitConfigGenerator implements Generator
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
        $defaults = [
            'ignore' => [],
            'export-ignore' => ['.gitignore', '.gitattributes', '.gitmodules'],
        ];

        if (isset($configuration['git'])) {
            $configuration['git'] = array_merge($defaults, $configuration['git']);
        } else {
            $configuration['git'] = $defaults;
        }

        $this->filesystem->dumpFile(
            '.gitignore',
            $this->twig->render(
                'gitignore.txt.twig',
                ['patterns' => array_unique($configuration['git']['ignore'])]
            )
        );

        $this->filesystem->dumpFile(
            '.gitattributes',
            $this->twig->render(
                'gitattributes.txt.twig',
                ['export_ignore' => array_unique($configuration['git']['export-ignore'])]
            )
        );
    }

    public function getConfigurators()
    {
        return [];
    }
}
