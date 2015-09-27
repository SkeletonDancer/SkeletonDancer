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

final class TravisConfigGenerator extends AbstractGenerator
{
    public function generate($phpMin, $enablePhpUnit, $enablePhpSpec, $enableBehat, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/.travis.yml',
            $this->twig->render(
                'travis.yml.twig',
                [
                    'phpMin' => $phpMin,
                    'enablePhpUnit' => $enablePhpUnit,
                    'enablePhpSpec' => $enablePhpSpec,
                    'enableBehat' => $enableBehat,
                ]
            )
        );
    }
}
