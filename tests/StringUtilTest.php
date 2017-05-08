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

namespace SkeletonDancer\Tests;

use PHPUnit\Framework\TestCase;
use SkeletonDancer\StringUtil;

final class StringUtilTest extends TestCase
{
    /** @test */
    public function it_converts_to_underscore()
    {
        self::assertEquals('camel_case', StringUtil::underscore('CamelCase'));
        self::assertEquals('snake_case', StringUtil::underscore('snake_Case'));
        self::assertEquals('camel_case', StringUtil::underscore('Camel_case'));
        self::assertEquals('under_score', StringUtil::underscore('under_score'));
        self::assertEquals('underscore', StringUtil::underscore('underscore'));
        self::assertEquals('foo_bar', StringUtil::underscore('foo.bar'));
        self::assertEquals('', StringUtil::underscore(''));
    }

    /** @test */
    public function it_converts_camel_case()
    {
        self::assertEquals('CamelCase', StringUtil::camelize('camel_case'));
        self::assertEquals('CamelCase', StringUtil::camelize('Camel_Case'));
        self::assertEquals('Camel_Case', StringUtil::camelize('Camel.Case'));
        self::assertEquals('Camel_Case', StringUtil::camelize('Camel\\Case'));
        self::assertEquals('CamelCase', StringUtil::camelize('Camel Case'));
        self::assertEquals('CamelCase', StringUtil::camelize('Camel  Case'));
        self::assertEquals('CamelCase', StringUtil::camelize('CamelCase'));
        self::assertEquals('', StringUtil::camelize(''));
    }

    /** @test */
    public function it_converts_camel_humps()
    {
        self::assertEquals('camelCase', StringUtil::camelHumps('camel_case'));
        self::assertEquals('camelCase', StringUtil::camelHumps('Camel_Case'));
        self::assertEquals('camel_Case', StringUtil::camelHumps('Camel.Case'));
        self::assertEquals('camel_Case', StringUtil::camelHumps('Camel\\Case'));
        self::assertEquals('camelCase', StringUtil::camelHumps('Camel Case'));
        self::assertEquals('camelCase', StringUtil::camelHumps('Camel  Case'));
        self::assertEquals('camelCase', StringUtil::camelHumps('CamelCase'));
        self::assertEquals('', StringUtil::camelize(''));
    }

    /** @test */
    public function it_humanizes_a_sentence()
    {
        self::assertEquals('Is active', StringUtil::humanize('is_active'));
        self::assertEquals('Is active', StringUtil::humanize('isActive'));
        self::assertEquals('', StringUtil::humanize(''));
    }

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

    /** @test */
    public function it_comments_lines()
    {
        self::assertEquals("#He\n#There", StringUtil::commentLines("He\nThere"));
        self::assertEquals("#He\n#There\n#", StringUtil::commentLines("He\nThere\n"));
        self::assertEquals("#He\r\n#There", StringUtil::commentLines("He\r\nThere"));
        self::assertEquals("#He\r\n#There\n#Me", StringUtil::commentLines("He\r\nThere\nMe"));
        self::assertEquals('#He there', StringUtil::commentLines('He there'));
        self::assertEquals('', StringUtil::commentLines(''));

        self::assertEquals("// He\n// There", StringUtil::commentLines("He\nThere", '// '));
        self::assertEquals("// He\n// There\n// ", StringUtil::commentLines("He\nThere\n", '// '));
        self::assertEquals("// He\r\n// There", StringUtil::commentLines("He\r\nThere", '// '));
        self::assertEquals('// He there', StringUtil::commentLines('He there', '// '));
    }

    /** @test */
    public function it_indents_lines()
    {
        self::assertEquals("    He\n    There", StringUtil::indentLines("He\nThere"));
        self::assertEquals("    He\n    There\n    ", StringUtil::indentLines("He\nThere\n"));
        self::assertEquals("    He\r\n    There", StringUtil::indentLines("He\r\nThere"));
        self::assertEquals("    He\r\n    There\n    Me", StringUtil::indentLines("He\r\nThere\nMe"));
        self::assertEquals('    He there', StringUtil::indentLines('He there'));
        self::assertEquals('', StringUtil::indentLines(''));

        self::assertEquals("        He\n        There", StringUtil::indentLines("He\nThere", 2));
        self::assertEquals("        He\n        There\n        ", StringUtil::indentLines("He\nThere\n", 2));
        self::assertEquals("        He\r\n        There", StringUtil::indentLines("He\r\nThere", 2));
        self::assertEquals("        He\r\n        There\n        Me", StringUtil::indentLines("He\r\nThere\nMe", 2));
        self::assertEquals('        He there', StringUtil::indentLines('He there', 2));
        self::assertEquals('', StringUtil::indentLines(''));

        self::assertEquals("\tHe\n\tThere", StringUtil::indentLines("He\nThere", 1, "\t"));
        self::assertEquals("\tHe\n\tThere\n\t", StringUtil::indentLines("He\nThere\n", 1, "\t"));
        self::assertEquals("\tHe\r\n\tThere", StringUtil::indentLines("He\r\nThere", 1, "\t"));
        self::assertEquals("\tHe\r\n\tThere\n\tMe", StringUtil::indentLines("He\r\nThere\nMe", 1, "\t"));
        self::assertEquals("\tHe there", StringUtil::indentLines('He there', 1, "\t"));
        self::assertEquals('', StringUtil::indentLines(''));
    }
}
