<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

final class ComposerGenerator extends AbstractGenerator
{
    public function generate($namespace,
        $type,
        $license,
        $author,
        $phpMin,
        $symfonyTest,
        $enablePhpUnit,
        $enablePhpSpec,
        $enableBehat,
        $workingDir
    )
    {
        $packageName = $this->generateComposerName($namespace);

        $this->filesystem->dumpFile(
            $workingDir.'/composer.json',
            $this->twig->render(
                'composer.json.twig',
                [
                    'name' => $packageName,
                    'type' => 'extension' === $type ? 'library' : $type,
                    'license' => $license,
                    'author' => $this->extractAuthor($author),
                    'phpMin' => $phpMin,
                    'namespace' => $namespace,

                    'symfonyTest' => $enablePhpUnit && $symfonyTest,
                    'enablePhpUnit' => $enablePhpUnit,
                    'enablePhpSpec' => $enablePhpSpec,
                    'enableBehat' => $enableBehat,
                ]
            )
        );
    }
}
