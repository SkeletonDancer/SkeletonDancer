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

final class GitConfigGenerator extends AbstractGenerator
{
    public function generate($enablePhpUnit, $enablePhpSpec, $enableBehat, $docFormat, $workingDir)
    {
        $this->filesystem->dumpFile(
            $workingDir.'/.gitignore',
            $this->twig->render(
                'gitignore.txt.twig',
                [
                    'phpUnitEnabled' => $enablePhpUnit,
                    'phpSpecEnabled' => $enablePhpSpec,
                    'behatEnabled' => $enableBehat,
                    'docFormat' => $docFormat,
                ]
            )
        );

        $this->filesystem->dumpFile(
            $workingDir.'/.gitattributes',
            $this->twig->render(
                'gitattributes.txt.twig',
                [
                    'phpUnitEnabled' => $enablePhpUnit,
                    'phpSpecEnabled' => $enablePhpSpec,
                    'behatEnabled' => $enableBehat,
                    'docFormat' => $docFormat,
                ]
            )
        );

        // IDE configuration should not be ignored by .gitignore
        $this->filesystem->dumpFile(
            $workingDir.'/.git/info/exclude',
            <<<OET
# git ls-files --others --exclude-from=.git/info/exclude
# Lines that start with '#' are comments.
# For a project mostly in C, the following would be a good set of
# exclude patterns (uncomment them if you want to use them):
# *.[oa]
# *~

.temp
Thumbs.db
*.bak
*.log
*.orig
*.vi
*.swp
*~

/.idea
OET
        );
    }
}
