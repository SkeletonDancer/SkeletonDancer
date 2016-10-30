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

namespace Rollerworks\Tools\SkeletonDancer\Configuration;

use Rollerworks\Tools\SkeletonDancer\ClassInitializer;
use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\DependentConfigurator;
use Rollerworks\Tools\SkeletonDancer\DependentGenerator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\OrderedConfigurator;
use Rollerworks\Tools\SkeletonDancer\OrderedGenerator;

/**
 * The Loader loads generators and configurators (and there dependencies)
 * and ensures the correct execution order.
 */
class ClassLoader
{
    /**
     * @var ClassInitializer
     */
    private $classInitializer;

    /**
     * @var ListSorter
     */
    private $configurators;

    /**
     * @var ListSorter
     */
    private $generators;

    public function __construct(ClassInitializer $classInitializer)
    {
        $this->classInitializer = $classInitializer;
    }

    public function clear()
    {
        $this->generators = new ListSorter(
            $this->classInitializer,
            Generator::class,
            OrderedGenerator::class,
            DependentGenerator::class
        );

        $this->configurators = new ListSorter(
            $this->classInitializer,
            Configurator::class,
            OrderedConfigurator::class,
            DependentConfigurator::class
        );
    }

    public function loadGeneratorClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->generators->addClass($class);
        }
    }

    public function loadConfiguratorClasses(array $classes)
    {
        foreach ($classes as $class) {
            $this->configurators->addClass($class);
        }
    }

    /**
     * Returns the array of configurator to execute.
     *
     * @return Configurator[]
     */
    public function getConfigurators(): array
    {
        // Load the configurators of the generators, this will resolve all
        // the generators and it's dependencies. But the result is cached.
        foreach ($this->generators->getInstances() as $generator) {
            foreach ((array) $generator->getConfigurators() as $configuratorClass) {
                $this->configurators->addClass($configuratorClass);
            }
        }

        return $this->configurators->getInstances();
    }

    /**
     * Returns the array of configurator to execute.
     *
     * @return Generator[]
     */
    public function getGenerators(): array
    {
        return $this->generators->getInstances();
    }
}
