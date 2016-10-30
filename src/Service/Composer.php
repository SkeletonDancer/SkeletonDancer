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

namespace Rollerworks\Tools\SkeletonDancer\Service;

class Composer
{
    private $process;

    public function __construct(CliProcess $process)
    {
        $this->process = $process;
    }

    public function requirePackage($name)
    {
        $name = (array) $name;
        $arguments = ['composer.phar', 'require', '--no-update'];

        $this->process->run(array_merge($arguments, $name));
    }

    public function requireDevPackage($name)
    {
        $name = (array) $name;
        $arguments = ['composer.phar', 'require', '--no-update', '--dev'];

        $this->process->run(array_merge($arguments, $name));
    }
}
