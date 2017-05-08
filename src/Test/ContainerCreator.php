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

namespace SkeletonDancer\Test;

use Prophecy\Prophecy\ObjectProphecy;
use SkeletonDancer\Container;
use SkeletonDancer\Service\CliProcess;
use SkeletonDancer\Service\Filesystem;
use SkeletonDancer\Service\Git;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Option;

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

        $this->container = new Container(
            [
                'current_dir' => __DIR__,
                'dancers_directory' => __DIR__.'/.dancer',
            ]
        );

        $format = ArgsFormat::build()
            ->addOption(new Option('overwrite', null, Option::REQUIRED_VALUE))
            ->getFormat();

        $this->container['console.args'] = (new Args($format))->setOption('overwrite', 'force');

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
