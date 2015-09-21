<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

final class ReadMeGenerator extends AbstractGenerator
{
    public function generate($name, $namespace, $phpMin, $workingDir)
    {
        $packageName = $this->generateComposerName($namespace);

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
