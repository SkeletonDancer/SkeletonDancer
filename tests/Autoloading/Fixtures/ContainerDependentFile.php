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

namespace SkeletonDancer\Tests\Autoloading\Fixtures;

use PHPUnit\Framework\Assert;

Assert::assertNotNull($this);
Assert::assertNotNull($this->container);

function iMustExist()
{
    return true;
}
