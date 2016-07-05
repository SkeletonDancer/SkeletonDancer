<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer;

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

    public function getNewInstance($className)
    {
        $r = new \ReflectionClass($className);

        if ($r->hasMethod('__construct')) {
            $methodReflection = $r->getMethod('__construct')->getParameters();
            $instanceArguments = [];

            foreach ($methodReflection as $parameter) {
                $instanceArguments[] = $this->resolveArgument($parameter);
            }

            return new $className(...$instanceArguments);
        }

        return new $className();
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
