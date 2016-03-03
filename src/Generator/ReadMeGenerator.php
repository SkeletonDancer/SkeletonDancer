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

final class ReadMeGenerator extends AbstractGenerator
{
    public function generate($name, $packageName, $phpMin, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/README.md',
            $this->twig->render(
                'readme.md.twig',
                [
                    'name' => $name,
                    'packageName' => $packageName,
                    'phpMin' => $phpMin,
                ]
            )
        );
    }
}
