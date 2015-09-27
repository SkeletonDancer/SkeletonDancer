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

final class LicenseGenerator extends AbstractGenerator
{
    public function generate($name, $author, $license, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/LICENSE',
            $this->twig->render(
                'Licenses/'.strtolower($license).'.txt.twig',
                [
                    'productName' => $name,
                    'author' => $this->extractAuthor($author),
                ]
            )
        );
    }
}
