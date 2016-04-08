<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\EventListener;

use Rollerworks\Tools\SkeletonDancer\ClassInitializer;
use Rollerworks\Tools\SkeletonDancer\Container;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Webmozart\Console\Api\Event\PreHandleEvent;

final class ExpressionFunctionsProviderSetupListener
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(PreHandleEvent $event)
    {
        if (isset($this->container['expression_functions_setup'])) {
            return;
        }

        $this->container['expression_functions_setup'] = true;

        /** @var ExpressionLanguage $expressionLanguage */
        $expressionLanguage = $this->container['expression_language'];

        /** @var ClassInitializer $classInitializer */
        $classInitializer = $this->container['class_initializer'];

        foreach ($this->container['config']->get(['expression_language', 'function_providers'], []) as $className) {
            $expressionLanguage->registerProvider($classInitializer->getNewInstance($className));
        }
    }
}
