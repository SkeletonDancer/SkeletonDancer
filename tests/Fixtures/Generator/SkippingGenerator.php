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

namespace SkeletonDancer\Tests\Fixtures\Generator;

use PHPUnit\Framework\Assert;
use SkeletonDancer\Generator;

class SkippingGenerator implements Generator
{
    public function generate(array $configuration): int
    {
        Assert::assertEquals(['name' => 'John'], $configuration);

        return self::STATUS_SKIPPED;
    }
}
