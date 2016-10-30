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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Mocks;

use Rollerworks\Tools\SkeletonDancer\Configuration\ClassLoader;

class ClassLoaderMock extends ClassLoader
{
    private $configurators = [];
    private $generators = [];

    public function __construct()
    {
        // no-op
    }

    public function clear()
    {
        // no-op
    }

    public function loadGeneratorClasses(array $classes)
    {
        $this->generators = $classes;
    }

    public function loadConfiguratorClasses(array $classes)
    {
        $this->configurators = $classes;
    }

    public function getConfigurators(): array
    {
        return $this->configurators;
    }

    public function getGenerators(): array
    {
        return $this->generators;
    }
}
