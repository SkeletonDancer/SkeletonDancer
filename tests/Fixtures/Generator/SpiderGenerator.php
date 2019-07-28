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

namespace Dance\Generator;

use PHPUnit\Framework\Assert;
use SkeletonDancer\Generator;
use SkeletonDancer\Service\Filesystem;

class SpiderGenerator implements Generator
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(array $configuration)
    {
        Assert::assertEquals(['name' => 'John'], $configuration);

        $this->filesystem->dumpFile('big.md', 'Does whatever');
        $this->filesystem->dumpFile('web.php', 'Yuck!');
    }
}
