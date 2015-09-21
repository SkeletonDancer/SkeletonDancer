<?php

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
