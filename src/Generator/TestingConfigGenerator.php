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

final class TestingConfigGenerator extends AbstractGenerator
{
    public function generate($name, $namespace, $enablePhpUnit, $enablePhpSpec, $enableBehat, $workingDir)
    {
        if ($enablePhpUnit) {
            $this->filesystem->dumpFile(
                $workingDir.'/phpunit.xml.dist',
                $this->twig->render(
                    'Testing/phpunit.xml.twig',
                    [
                        'name' => $name,
                        'namespace' => $namespace,
                    ]
                )
            );
        }

        if ($enablePhpSpec) {
            $this->filesystem->dumpFile(
                $workingDir.'/phpspec.yml.dist',
                $this->twig->render(
                    'Testing/phpspec.yml.twig',
                    [
                        'name' => $name,
                        'shortName' => $this->shortProductName($name),
                        'namespace' => $namespace,
                    ]
                )
            );
        }

        if ($enableBehat) {
            $this->filesystem->dumpFile(
                $workingDir.'/behat.yml.dist',
                $this->twig->render(
                    'Testing/behat.yml.twig',
                    [
                        'name' => $name,
                        'shortName' => $this->shortProductName($name),
                        'namespace' => $namespace,
                    ]
                )
            );
        }
    }
}
