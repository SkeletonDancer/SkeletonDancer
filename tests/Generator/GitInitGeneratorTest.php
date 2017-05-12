<?php

declare(strict_types=1);

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SkeletonDancer\Tests\Generator;

use SkeletonDancer\Test\GeneratorTestCase;

final class GitInitGeneratorTest extends GeneratorTestCase
{
    /** @test */
    public function it_initialized_a_git_repository()
    {
        $this->git->isGitDirectory(false)->willReturn(false);
        $this->git->initRepo()->shouldBeCalled();

        $this->runGenerator();
    }

    /** @test */
    public function it_does_nothing_when_its_already_a_git_repository()
    {
        $this->git->isGitDirectory(false)->willReturn(true);
        $this->git->initRepo()->shouldNotBeCalled();

        $this->runGenerator();
    }
}
