<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Configurator;

use Rollerworks\Tools\SkeletonDancer\ClassInitializer;
use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\DependentConfigurator;
use Rollerworks\Tools\SkeletonDancer\Exception\CircularReferenceException;
use Rollerworks\Tools\SkeletonDancer\Generator;

/**
 * The configurator Loader loads configurators (and dependencies)
 * and ensures the correct execution order.
 *
 * This class is was borrowed from the Doctrine DataFixtures project.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class Loader
{
    /**
     * @var ClassInitializer
     */
    private $classInitializer;

    /**
     * @var Configurator[]|DependentConfigurator[]
     */
    private $configurators = [];

    /**
     * Array of ordered configurator object instances.
     *
     * @var array
     */
    private $orderedConfigurators = [];

    /**
     * Determines if we must order configurators by there dependencies.
     *
     * @var bool
     */
    private $orderConfiguratorsByDependencies = false;

    /**
     * Constructor.
     *
     * @param ClassInitializer $classInitializer
     */
    public function __construct(ClassInitializer $classInitializer)
    {
        $this->classInitializer = $classInitializer;
    }

    /**
     * Clears the current list of configurators.
     */
    public function clear()
    {
        $this->configurators = [];
    }

    /**
     * Load Configurators from a Generator.
     *
     * @param Generator|string $generator
     *
     * @return Generator
     */
    public function loadFromGenerator($generator)
    {
        if (!$generator instanceof Generator) {
            $generator = $this->classInitializer->getNewInstance($generator);
        }

        foreach ((array) $generator->getConfigurators() as $configuratorClass) {
            if (!isset($this->configurators[$configuratorClass])) {
                $this->addConfigurator($this->classInitializer->getNewInstance($configuratorClass));
            }
        }

        return $generator;
    }

    /**
     * Add a Generator object instance to the resolver.
     *
     * @param Configurator $configurator
     */
    public function addConfigurator(Configurator $configurator)
    {
        $configuratorClass = get_class($configurator);

        if (isset($this->configurators[$configuratorClass])) {
            return;
        }

        $this->configurators[$configuratorClass] = $configurator;

        if ($configurator instanceof DependentConfigurator) {
            $this->orderConfiguratorsByDependencies = true;

            foreach ($configurator->getDependencies() as $class) {
                $this->addConfigurator($this->classInitializer->getNewInstance($class));
            }
        }
    }

    /**
     * Returns the array of configurator to execute.
     *
     * @return Configurator[]
     */
    public function getConfigurators()
    {
        $this->orderedConfigurators = [];

        if ($this->orderConfiguratorsByDependencies) {
            $this->orderConfiguratorsByDependencies();
        } else {
            $this->orderedConfigurators = $this->configurators;
        }

        return $this->orderedConfigurators;
    }

    /**
     * Orders configurator by dependencies.
     */
    private function orderConfiguratorsByDependencies()
    {
        $sequenceForClasses = [];

        // First we determine which classes has dependencies and which don't.
        foreach ($this->configurators as $configurator) {
            $configuratorClass = get_class($configurator);

            if ($configurator instanceof DependentConfigurator) {
                $dependenciesClasses = $configurator->getDependencies();

                if (!is_array($dependenciesClasses) || !count($dependenciesClasses)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Method "%s" in class "%s" must return an array of classes which are dependencies for the configurator, and it must be NOT empty.',
                            'getDependencies',
                            $configuratorClass
                        )
                    );
                }

                if (in_array($configuratorClass, $dependenciesClasses, true)) {
                    throw new \InvalidArgumentException(
                        sprintf('Class "%s" can\'t have itself as a dependency', $configuratorClass)
                    );
                }

                // We mark this class as unsequenced
                $sequenceForClasses[$configuratorClass] = -1;
            } else {
                // This class has no dependencies, so we assign 0
                $sequenceForClasses[$configuratorClass] = 0;
            }
        }

        // Now we order configurator by sequence
        $sequence = 1;
        $lastCount = -1;

        while (($count = count($unsequencedClasses = $this->getUnsequencedClasses($sequenceForClasses))) > 0 && $count !== $lastCount) {
            foreach ($unsequencedClasses as $key => $class) {
                $configurator = $this->configurators[$class];
                $dependencies = $configurator->getDependencies();
                $unsequencedDependencies = $this->getUnsequencedClasses($sequenceForClasses, $dependencies);

                if (count($unsequencedDependencies) === 0) {
                    $sequenceForClasses[$class] = $sequence++;
                }
            }

            $lastCount = $count;
        }

        $orderedConfigurator = [];

        // If there're configurator non-sequenced left and they couldn't be sequenced,
        // it means we have a circular reference
        if ($count > 0) {
            throw new CircularReferenceException($unsequencedClasses);
        } else {
            // We order the classes by sequence
            asort($sequenceForClasses);

            foreach ($sequenceForClasses as $class => $sequence) {
                // If configurator were ordered
                $orderedConfigurator[] = $this->configurators[$class];
            }
        }

        $this->orderedConfigurators = array_merge($this->orderedConfigurators, $orderedConfigurator);
    }

    private function getUnsequencedClasses(array $sequences, array $classes = null)
    {
        $unsequencedClasses = [];

        if (null === $classes) {
            $classes = array_keys($sequences);
        }

        foreach ($classes as $class) {
            if (-1 === $sequences[$class]) {
                $unsequencedClasses[] = $class;
            }
        }

        return $unsequencedClasses;
    }
}
