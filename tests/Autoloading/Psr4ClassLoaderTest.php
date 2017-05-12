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

namespace SkeletonDancer\Tests\Autoloading;

use PHPUnit\Framework\TestCase;
use SkeletonDancer\Autoloading\Psr4ClassLoader;

final class Psr4ClassLoaderTest extends TestCase
{
    /**
     * @param string $className
     * @dataProvider getLoadClassTests
     */
    public function testLoadClass(string $className)
    {
        $loader = new Psr4ClassLoader();
        $loader->addPrefix(
            'Acme\\DemoLib',
            __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'psr-4'
        );
        $loader->loadClass($className);
        self::assertTrue(class_exists($className), sprintf('loadClass() should load %s', $className));
    }

    /**
     * @return array
     */
    public function getLoadClassTests(): array
    {
        return [
            ['Acme\\DemoLib\\Foo'],
            ['Acme\\DemoLib\\Class_With_Underscores'],
            ['Acme\\DemoLib\\Lets\\Go\\Deeper\\Foo'],
            ['Acme\\DemoLib\\Lets\\Go\\Deeper\\Class_With_Underscores'],
        ];
    }

    /**
     * @param string $className
     * @dataProvider getLoadNonexistentClassTests
     */
    public function testLoadNonexistentClass(string $className)
    {
        $loader = new Psr4ClassLoader();
        $loader->addPrefix(
            'Acme\\DemoLib',
            __DIR__.DIRECTORY_SEPARATOR.'Fixtures'.DIRECTORY_SEPARATOR.'psr-4'
        );
        $loader->loadClass($className);

        self::assertFalse(class_exists($className), sprintf('loadClass() should not load %s', $className));
    }

    public function getLoadNonexistentClassTests(): array
    {
        return [
            ['Acme\\DemoLib\\I_Do_Not_Exist'],
            ['UnknownVendor\\SomeLib\\I_Do_Not_Exist'],
        ];
    }
}
