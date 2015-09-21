<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

final class ComposerGenerator extends AbstractGenerator
{
    public function generate($namespace, $type, $license, $author, $phpMin, $symfonyTest, $workingDir)
    {
        $packageName = $this->generateComposerName($namespace);

        $this->filesystem->dumpFile(
            $workingDir.'/composer.json',
            $this->twig->render(
                'composer.json.twig',
                [
                    'name' => $packageName,
                    'type' => $type,
                    'license' => $license,
                    'author' => $this->extractAuthor($author),
                    'phpMin' => $phpMin,
                    'symfonyTest' => $symfonyTest,
                    'namespace' => $namespace,
                ]
            )
        );
    }

    private function extractAuthor($author)
    {
        return [
            'name' => substr($author, 0, strpos($author, '<')),
            'email' => substr($author, strpos($author, '<') + 1, -1),
        ];
    }
}
