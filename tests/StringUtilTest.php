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

namespace Rollerworks\Tools\SkeletonDancer\Tests;

use Rollerworks\Tools\SkeletonDancer\StringUtil;

final class StringUtilTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_gets_the_Nth_dirname()
    {
        self::assertEquals('Generators', StringUtil::getNthDirname('src/Generators/', 1));
        self::assertEquals('Generators', StringUtil::getNthDirname('src/Generators', 1));
        self::assertEquals('Generators', StringUtil::getNthDirname('Generators', 0));

        self::assertEquals('src', StringUtil::getNthDirname('src/Generators/', 0));
        self::assertEquals('src', StringUtil::getNthDirname('src\\Generators/', 0));
        self::assertEquals('NotExistent', StringUtil::getNthDirname('Generators/', 1, 'NotExistent'));

        // Reverse look-up
        self::assertEquals('Deeper', StringUtil::getNthDirname('src/Generators/Foo/Deeper', -1));
        self::assertEquals('Foo', StringUtil::getNthDirname('src/Generators/Foo/Deeper', -2));
        self::assertEquals('src', StringUtil::getNthDirname('src/', -1));
        self::assertEquals('', StringUtil::getNthDirname('src/', -2));
    }
}
