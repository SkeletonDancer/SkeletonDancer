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
use SkeletonDancer\Autoloading\AutoloadingSetup;
use SkeletonDancer\Autoloading\Psr4ClassLoader;
use SkeletonDancer\Container;
use SkeletonDancer\Dance;

final class AutoloadingSetupTest extends TestCase
{
    /** @test */
    public function it_registers_psr4_mappings()
    {
        $autoloaderProphecy = $this->prophesize(Psr4ClassLoader::class);
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Generator', '/home/me/.dancer/SkeletonDancer/empty/Generator')->shouldBeCalled();
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Questioner', '/home/me/.dancer/SkeletonDancer/empty/Questioner')->shouldBeCalled();

        $dance = new Dance('SkeletonDancer/empty', '/home/me/.dancer/SkeletonDancer/empty');
        $dance->autoloading['psr-4']['SkeletonDancer\\Generator'] = 'Generator';
        $dance->autoloading['psr-4']['SkeletonDancer\\Questioner'] = 'Questioner';

        $autoloadingSetup = new AutoloadingSetup($autoloaderProphecy->reveal(), new Container());
        $autoloadingSetup->setUpFor($dance);
    }

    /** @test */
    public function it_registers_psr4_mappings_with_multiple_dirs_for_a_prefix()
    {
        $autoloaderProphecy = $this->prophesize(Psr4ClassLoader::class);
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Generator', '/home/me/.dancer/SkeletonDancer/empty/Generator')->shouldBeCalled();
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Questioner', '/home/me/.dancer/SkeletonDancer/empty/Questioner')->shouldBeCalled();
        $autoloaderProphecy->addPrefix('SkeletonDancer2\\', '/home/me/.dancer/SkeletonDancer/empty/Questioner')->shouldBeCalled();
        $autoloaderProphecy->addPrefix('SkeletonDancer2\\', '/home/me/.dancer/SkeletonDancer/empty/Generator')->shouldBeCalled();

        $dance = new Dance('SkeletonDancer/empty', '/home/me/.dancer/SkeletonDancer/empty');
        $dance->autoloading['psr-4']['SkeletonDancer\\Generator'] = 'Generator';
        $dance->autoloading['psr-4']['SkeletonDancer\\Questioner'] = 'Questioner';
        $dance->autoloading['psr-4']['SkeletonDancer2\\'] = ['Questioner', 'Generator'];

        $autoloadingSetup = new AutoloadingSetup($autoloaderProphecy->reveal(), new Container());
        $autoloadingSetup->setUpFor($dance);
    }

    /** @test */
    public function it_registers_psr4_mappings_with_windows_directory_separator()
    {
        $autoloaderProphecy = $this->prophesize(Psr4ClassLoader::class);
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Generator', 'c:\\Users\\me/.dancer/SkeletonDancer/empty/Generator')->shouldBeCalled();
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Questioner', 'c:\\Users\\me/.dancer/SkeletonDancer/empty/Questioner')->shouldBeCalled();

        $dance = new Dance('SkeletonDancer/empty', 'c:\\Users\\me/.dancer/SkeletonDancer/empty');
        $dance->autoloading['psr-4']['SkeletonDancer\\Generator'] = 'Generator';
        $dance->autoloading['psr-4']['SkeletonDancer\\Questioner'] = 'Questioner';

        $autoloadingSetup = new AutoloadingSetup($autoloaderProphecy->reveal(), new Container());
        $autoloadingSetup->setUpFor($dance);
    }

    /** @test */
    public function it_does_not_error_when_psr4_is_missing()
    {
        $autoloaderProphecy = $this->prophesize(Psr4ClassLoader::class);
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Generator', '/home/me/.dancer/SkeletonDancer/empty/Generator')->shouldNotBeCalled();

        $dance = new Dance('SkeletonDancer/empty', '/home/me/.dancer/SkeletonDancer/empty');

        $autoloadingSetup = new AutoloadingSetup($autoloaderProphecy->reveal(), new Container());
        $autoloadingSetup->setUpFor($dance);
    }

    /** @test */
    public function it_loads_single_files()
    {
        $autoloaderProphecy = $this->prophesize(Psr4ClassLoader::class);
        $autoloaderProphecy->addPrefix('SkeletonDancer\\Generator', '/home/me/.dancer/SkeletonDancer/empty/Generator')->shouldNotBeCalled();

        $dance = new Dance('SkeletonDancer/empty', __DIR__.'/Fixtures');
        $dance->autoloading['files'][] = 'ContainerDependentFile.php';

        $autoloadingSetup = new AutoloadingSetup($autoloaderProphecy->reveal(), new Container());

        self::assertFalse(function_exists('\SkeletonDancer\Tests\Autoloading\Fixtures\iMustExist'), 'File is already loaded');

        $autoloadingSetup->setUpFor($dance);

        self::assertTrue(function_exists('\SkeletonDancer\Tests\Autoloading\Fixtures\iMustExist'), 'File is not loaded');
    }
}
