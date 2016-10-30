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
use Rollerworks\Tools\SkeletonDancer\Exception\CircularReferenceException;

/**
 * The ListSorter loads class instances (and dependencies)
 * and ensures the correct order.
 *
 * This class is was borrowed from the Doctrine DataFixtures project.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 *
 * @internal
 */
final class ListSorter
{
    /**
     * @var ClassInitializer
     */
    private $classInitializer;

    /**
     * @var array
     */
    private $instances = [];

    /**
     * Array of ordered class object instances.
     *
     * @var array
     */
    private $orderedInstances = [];

    /**
     * Determines if we must order configurators by a number.
     *
     * @var bool
     */
    private $orderByNumber = false;

    /**
     * Determines if we must order configurators by there dependencies.
     *
     * @var bool
     */
    private $orderByDependencies = false;

    private $baseClassName;
    private $orderedClassName;
    private $dependentClassName;

    public function __construct(
        ClassInitializer $classInitializer,
        string $baseClassName,
        string $orderedClassName,
        string $dependentClassName
    ) {
        $this->classInitializer = $classInitializer;
        $this->baseClassName = $baseClassName;
        $this->orderedClassName = $orderedClassName;
        $this->dependentClassName = $dependentClassName;
    }

    /**
     * Add a Generator object instance to the resolver.
     *
     * @param string $class
     */
    public function addClass($class)
    {
        $class = ltrim($class, '\\');

        if (isset($this->instances[ltrim($class, '\\')])) {
            return;
        }

        $this->add($this->classInitializer->getNewInstance($class));
    }

    /**
     * Add a Generator object instance to the resolver.
     *
     * @param object $object
     */
    public function add($object)
    {
        $class = get_class($object);

        if (isset($this->instances[$class])) {
            return;
        }

        if ($object instanceof $this->orderedClassName && $object instanceof $this->dependentClassName) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class "%s" can\'t implement "%s" and "%s" at the same time.',
                    get_class($object),
                    $this->orderedClassName,
                    $this->dependentClassName
                )
            );
        }

        $this->instances[$class] = $object;

        if ($object instanceof $this->orderedClassName) {
            $this->orderByNumber = true;
        } elseif ($object instanceof $this->dependentClassName) {
            $this->orderByDependencies = true;

            foreach ($object->getDependencies() as $class) {
                $this->add($this->classInitializer->getNewInstance($class));
            }
        }
    }

    /**
     * Returns the array of ordered and resolved classes.
     *
     * @return array
     */
    public function getInstances(): array
    {
        if ($this->orderByNumber) {
            $this->orderConfiguratorsByNumber();
        }

        if ($this->orderByDependencies) {
            $this->orderConfiguratorsByDependencies();
        }

        if (!$this->orderByNumber && !$this->orderByDependencies) {
            $this->orderedInstances = $this->instances;
        }

        return $this->orderedInstances;
    }

    /**
     * Orders configurators by number.
     */
    private function orderConfiguratorsByNumber()
    {
        $this->orderedInstances = $this->instances;

        usort(
            $this->orderedInstances,
            function ($a, $b) {
                if ($a instanceof $this->orderedClassName && $b instanceof $this->orderedClassName) {
                    return $a->getOrder() <=> $b->getOrder();
                }

                $a1 = $a instanceof $this->orderedClassName ? $a->getOrder() : 0;
                $b1 = $b instanceof $this->orderedClassName ? $b->getOrder() : 0;

                return $a1 <=> $b1;
            }
        );
    }

    /**
     * Orders classes by dependencies.
     */
    private function orderConfiguratorsByDependencies()
    {
        $sequenceForClasses = [];

        // If instance were already ordered by number then we need
        // to remove classes which are not instances of $this->orderedClassName
        // in case a instance implementing $this->dependentClassName exist.
        // This is because, in that case, the method orderConfiguratorByDependencies
        // will handle all configurators which are not instances of
        // $this->orderedClassName.
        if ($this->orderByNumber) {
            $count = count($this->orderedInstances);

            foreach ($count as $i) {
                if (!($this->orderedInstances[$i] instanceof $this->orderedClassName)) {
                    unset($this->orderedInstances[$i]);
                }
            }
        }

        // First we determine which classes has dependencies and which don't.
        foreach ($this->instances as $instance) {
            $configuratorClass = get_class($instance);

            if ($instance instanceof $this->orderedClassName) {
                continue;
            }

            if ($instance instanceof $this->dependentClassName) {
                $dependenciesClasses = $instance->getDependencies();

                if (!count($dependenciesClasses)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Method "%s" in class "%s" must return an array of classes with dependencies, and it must be NOT empty.',
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

        // Now we order instance by sequence
        $sequence = 1;
        $lastCount = -1;

        while (($count = count($unsequencedClasses = $this->getUnsequencedClasses($sequenceForClasses))) > 0 && $count !== $lastCount) {
            foreach ($unsequencedClasses as $key => $class) {
                $instance = $this->instances[$class];
                $dependencies = $instance->getDependencies();
                $unsequencedDependencies = $this->getUnsequencedClasses($sequenceForClasses, $dependencies);

                if (count($unsequencedDependencies) === 0) {
                    $sequenceForClasses[$class] = $sequence++;
                }
            }

            $lastCount = $count;
        }

        $orderedInstance = [];

        // If there're instances non-sequenced left and they couldn't be sequenced,
        // it means we have a circular reference
        if ($count > 0) {
            throw new CircularReferenceException($unsequencedClasses);
        }

        // We order the classes by sequence
        asort($sequenceForClasses);

        foreach ($sequenceForClasses as $class => $sequence) {
            // If instance were ordered
            $orderedInstance[] = $this->instances[$class];
        }

        $this->orderedInstances = array_merge($this->orderedInstances, $orderedInstance);
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
