<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Generator\Symfony;

use Rollerworks\Tools\SkeletonDancer\Configurator\GeneralConfigurator;
use Rollerworks\Tools\SkeletonDancer\Configurator\Symfony\BundleConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class BundleGenerator implements Generator
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
            $configuration['src_dir_norm'].$configuration['sf_bundle_name'].'.php',
            $this->twig->render(
                'SfBundle/bundle.php.twig',
                [
                    'name' => $configuration['name'],
                    'namespace' => $configuration['namespace'],
                    'bundle' => $configuration['sf_bundle_name'],
                    'extension_name' => $configuration['sf_extension_name'],
                    'extension_alias' => $configuration['sf_extension_name'],
                ]
            )
        );

        $this->filesystem->dumpFile(
            $configuration['src_dir_norm'].'DependencyInjection/'.$configuration['sf_extension_name'].'Extension.php',
            $this->twig->render(
                'SfBundle/extension.php.twig',
                [
                    'name' => $configuration['name'],
                    'namespace' => $configuration['namespace'],
                    'bundle' => $configuration['sf_bundle_name'],
                    'format' => $configuration['sf_bundle_config_format'],
                    'extension_name' => $configuration['sf_extension_name'],
                    'extension_alias' => $configuration['sf_extension_alias'],
                ]
            )
        );

        $this->filesystem->dumpFile(
            $configuration['src_dir_norm'].'DependencyInjection/Configuration.php',
            $this->twig->render(
                'SfBundle/configuration.php.twig',
                [
                    'name' => $configuration['sf_extension_alias'],
                    'namespace' => $configuration['namespace'],
                ]
            )
        );

        $this->filesystem->dumpFile(
            $configuration['src_dir_norm'].'Resources/config/services/core.'.$configuration['sf_bundle_config_format'],
            $this->twig->render(
                'SfBundle/services.'.$configuration['sf_bundle_config_format'].'.twig',
                [
                    'namespace' => $configuration['namespace'],
                    'extension_alias' => $configuration['sf_extension_alias'],
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [
            GeneralConfigurator::class,
            BundleConfigurator::class,
        ];
    }
}
