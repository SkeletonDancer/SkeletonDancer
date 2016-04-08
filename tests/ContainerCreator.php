<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Tests;

use Prophecy\Prophecy\ObjectProphecy;
use Rollerworks\Tools\SkeletonDancer\Container;
use Rollerworks\Tools\SkeletonDancer\Service\CliProcess;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;
use Rollerworks\Tools\SkeletonDancer\Service\Git;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Method annotations to suppress analyzer warnings.
 *
 * @method prophesize($class)
 * @method getMockWithoutInvokingTheOriginalConstructor($class)
 */
trait ContainerCreator
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ObjectProphecy
     */
    protected $filesystem;

    /**
     * @var ObjectProphecy
     */
    protected $cliProcess;

    /**
     * @var ObjectProphecy
     */
    protected $git;

    protected function setUpContainer()
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->cliProcess = $this->prophesize(CliProcess::class);
        $this->git = $this->prophesize(Git::class);

        $this->container = new Container();
        $this->container['style'] = function () {
            return $this->getMockWithoutInvokingTheOriginalConstructor(SymfonyStyle::class);
        };

        $this->container['filesystem'] = function () {
            return $this->filesystem->reveal();
        };

        $this->container['process'] = function () {
            return $this->cliProcess->reveal();
        };

        $this->container['git'] = function () {
            return $this->git->reveal();
        };
    }
}
