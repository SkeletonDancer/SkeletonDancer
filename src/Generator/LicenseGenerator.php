<?php

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
