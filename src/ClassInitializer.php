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

namespace SkeletonDancer;

use Pimple\Container as ServiceLocator;

final class ClassInitializer
{
    /**
     * @var ServiceLocator
     */
    private $container;

    public function __construct(ServiceLocator $container)
    {
        $this->container = $container;
    }

    public function getNewInstance(string $className, string $expectedClass = null)
    {
        $r = new \ReflectionClass($className);

        if ($r->hasMethod('__construct')) {
            $methodReflection = $r->getMethod('__construct')->getParameters();
            $instanceArguments = [];

            foreach ($methodReflection as $parameter) {
                $instanceArguments[] = $this->resolveArgument($parameter);
            }

            $instance = new $className(...$instanceArguments);
        } else {
            $instance = new $className();
        }

        if (null !== $expectedClass && !$instance instanceof $expectedClass) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is expected to implement "%s".', $className, $expectedClass));
        }

        return $instance;
    }

    private function resolveArgument(\ReflectionParameter $parameter)
    {
        $name = StringUtil::underscore($parameter->name);

        if ('container' === $name) {
            return $this->container;
        }

        if (isset($this->container[$name])) {
            return $this->container[$name];
        }

        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        throw new \RuntimeException(
            sprintf(
                'Unable to resolve parameter "%s" of class "%s" no service/parameter found with name "%s". '.
                'Consider adding a default value.',
                $name,
                $parameter->getDeclaringClass()->name,
                $name
            )
        );
    }
}
