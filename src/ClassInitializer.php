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
use SkeletonDancer\Service\DanceAware;
use SkeletonDancer\Service\LazyService;

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

    public function getNewInstanceFor(Dance $dance, string $className, string $expectedInterfaceType = null): object
    {
        $this->loadClass($className, $dance->directory);

        $r = new \ReflectionClass($className);

        if ($r->hasMethod('__construct')) {
            $methodReflection = $r->getMethod('__construct')->getParameters();
            $instanceArguments = [];

            foreach ($methodReflection as $parameter) {
                $instanceArguments[] = $this->resolveArgument($parameter, $dance);
            }

            $instance = new $className(...$instanceArguments);
        } else {
            $instance = new $className();
        }

        if (null !== $expectedInterfaceType && !$instance instanceof $expectedInterfaceType) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is expected to implement "%s".', $className, $expectedInterfaceType));
        }

        return $instance;
    }

    private function loadClass(string $className, string $directory): void
    {
        $classParts = explode('\\', $className);

        if ('Dance' !== array_shift($classParts)) {
            throw new \InvalidArgumentException(sprintf('Dance provided classes are expected to begin with `Dance` for class "%s".', $className));
        }

        if (class_exists($className)) {
            return;
        }

        $expectedFilename = implode('/', $classParts).'.php';

        if (!file_exists($directory.'/'.$expectedFilename)) {
            throw new \InvalidArgumentException(sprintf('Unable to locate file %s in directory %s.', $expectedFilename, $directory));
        }

        require $directory.'/'.$expectedFilename;

        if (!class_exists($className, false)) {
            throw new \InvalidArgumentException(sprintf('Class %s was expected to be defined in %s', $className, $directory.'/'.$expectedFilename));
        }
    }

    private function resolveArgument(\ReflectionParameter $parameter, Dance $dance)
    {
        $name = StringUtil::underscore($parameter->name);

        if ('container' === $name) {
            return $this->container;
        }

        if (isset($this->container[$name])) {
            $service = $this->container[$name];

            if ($service instanceof DanceAware) {
                $service->setDance($dance);
            }

            if ($service instanceof LazyService) {
                return $service->getInstance();
            }

            return $service;
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
