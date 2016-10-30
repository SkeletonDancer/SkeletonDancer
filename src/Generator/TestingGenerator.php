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
use Rollerworks\Tools\SkeletonDancer\Configurator\TestingConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;

final class TestingGenerator implements Generator
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
        if ($configuration['enable_phpunit']) {
            $this->filesystem->dumpFile(
                'phpunit.xml.dist',
                $this->twig->render(
                    'Testing/phpunit.xml.twig',
                    [
                        'name' => $configuration['name'],
                        'namespace' => $configuration['namespace'],
                    ]
                )
            );
        }

        if ($configuration['enable_phpspec']) {
            $this->filesystem->dumpFile(
                'phpspec.yml.dist',
                $this->twig->render(
                    'Testing/phpspec.yml.twig',
                    [
                        'name' => $configuration['name'],
                        'shortName' => $configuration['phpspec_suite_name'],
                        'namespace' => $configuration['namespace'],
                    ]
                )
            );
        }

        if ($configuration['enable_behat']) {
            $this->filesystem->dumpFile(
                'behat.yml.dist',
                $this->twig->render(
                    'Testing/behat.yml.twig',
                    [
                        'name' => $configuration['name'],
                        'shortName' => $configuration['behat_suite_name'],
                        'namespace' => $configuration['namespace'],
                    ]
                )
            );
        }
    }

    public function getConfigurators()
    {
        return [GeneralConfigurator::class, TestingConfigurator::class];
    }
}
