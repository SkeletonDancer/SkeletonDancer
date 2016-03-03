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

use Symfony\Component\Process\ProcessBuilder;

final class ComposerGenerator extends AbstractGenerator
{
    public function generate(
        $name,
        $namespace,
        $type,
        $license,
        $author,
        $phpMin,
        $symfonyTest,
        $enablePhpUnit,
        $enablePhpSpec,
        $enableBehat,
        $workingDir
    ) {
        $this->filesystem->dumpFile(
            $workingDir.'/composer.json',
            $this->twig->render(
                'composer.json.twig',
                [
                    'name' => $name,
                    'type' => 'extension' === $type ? 'library' : $type,
                    'license' => $license,
                    'author' => $this->extractAuthor($author),
                    'phpMin' => $phpMin,
                    'namespace' => $namespace,
                ]
            )
        );

        $packages = [];

        if ($enablePhpSpec) {
            $packages[] = 'phpspec/phpspec';
        }

        if ($enablePhpUnit && $symfonyTest) {
            $packages[] = 'symfony/phpunit-bridge';
        }

        if ($enableBehat) {
            $packages[] = 'behat/behat';
            $packages[] = 'behat/symfony2-extension';
            $packages[] = 'behat/mink-extension';
            $packages[] = 'behat/mink-browserkit-driver';
            $packages[] = 'behat/mink-selenium2-driver';
            $packages[] = 'behat/mink';
            $packages[] = 'lakion/mink-debug-extension';
        }

        $this->requireDev($packages, $workingDir);
    }

    private function requireDev($name, $workingDir)
    {
        $name = (array) $name;

        (new ProcessBuilder(array_merge(['composer.phar', 'require', '--dev', '--no-update'], $name) ))->setWorkingDirectory($workingDir)->getProcess()->run();
    }
}
