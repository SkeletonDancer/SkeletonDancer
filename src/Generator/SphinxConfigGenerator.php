<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

final class SphinxConfigGenerator extends AbstractGenerator
{
    public function generate($name, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/doc/conf.py',
            $this->twig->render(
                'sphinx.py.twig',
                [
                    'name' => $name,
                    'shortName' => $this->shortProductName($name),
                ]
            )
        );

        $filePathPrefix = __DIR__.'/../../Resources/BuildScripts/sphinx-';
        $this->filesystem->copy($filePathPrefix.'bat', $workingDir.'/doc/make.bat');
        $this->filesystem->copy($filePathPrefix.'makefile', $workingDir.'/doc/Makefile');
    }
}
