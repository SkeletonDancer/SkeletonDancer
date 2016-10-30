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

use Rollerworks\Tools\SkeletonDancer\Configurator\DocumentationConfigurator;
use Rollerworks\Tools\SkeletonDancer\Configurator\GeneralConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class DocumentationGenerator implements Generator
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
        if ('rst' === $configuration['doc_format']) {
            $this->filesystem->dumpFile(
                'doc/conf.py',
                $this->twig->render(
                    'sphinx.py.twig',
                    [
                        'name' => $configuration['name'],
                        'shortName' => $configuration['rst_short_name'],
                    ]
                )
            );

            $filePathPrefix = __DIR__.'/../../Resources/BuildScripts/sphinx-';
            $this->filesystem->copy($filePathPrefix.'bat', 'doc/make.bat');
            $this->filesystem->copy($filePathPrefix.'makefile', 'doc/Makefile');
        }
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class, DocumentationConfigurator::class];
    }
}
