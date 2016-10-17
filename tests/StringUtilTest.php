<?php

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
    }
}
