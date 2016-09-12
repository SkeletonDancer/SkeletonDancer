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

use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Service\Git;

final class GitInitGenerator implements Generator
{
    private $git;

    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    public function generate(array $configuration)
    {
        if (!$this->git->isGitDirectory(false)) {
            $this->git->initRepo();
        }
    }

    public function getConfigurators()
    {
        return [];
    }
}
