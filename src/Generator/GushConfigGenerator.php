<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

final class GushConfigGenerator extends AbstractGenerator
{
    public function generate($name, $author, $license, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/.gush.yml',
            $this->twig->render(
                'gush.yml.twig',
                [
                    'name' => $name,
                    'author' => $author,
                    'license' => $license,
                ]
            )
        );
    }
}
