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

final class PhpCsGenerator extends AbstractGenerator
{
    public function generate($name, $author, $license, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/.php_cs',
            $this->twig->render(
                'php_cs.php.twig',
                [
                    'name' => $name,
                    'author' => $author,
                    'license' => $license,
                ]
            )
        );
    }
}
