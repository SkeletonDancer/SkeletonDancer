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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Generator;

use Prophecy\Argument;

final class ReadMeGeneratorTest extends GeneratorTestCase
{
    /**
     * @test
     */
    public function it_generates_a_README_file()
    {
        $this->filesystem->dumpFile('README.md', Argument::containingString('SkeletonDancer'))->shouldBeCalled();

        $this->runGenerator(
            [
                'name' => 'SkeletonDancer',
                'package_name' => 'rollerworks/skeleton-dancer',
                'namespace' => 'Rollerworks\\Tools\\SkeletonDancer',
                'author_name' => 'Sebastiaan Stok',
                'author_email' => 's.stok@rollerscapes.net',
                'php_min' => '5.5',
            ]
        );
    }
}
