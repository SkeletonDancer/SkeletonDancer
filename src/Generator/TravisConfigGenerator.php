<?php

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
