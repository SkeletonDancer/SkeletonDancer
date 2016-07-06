<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Tests\Configurator;

use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Questioner\UsingDefaultsQuestioner;
use Rollerworks\Tools\SkeletonDancer\Tests\ContainerCreator;

/**
 * Acceptance test-case Configurator.
 */
abstract class ConfiguratorTestCase extends \PHPUnit_Framework_TestCase
{
    use ContainerCreator;

    /**
     * @var Configurator
     */
    protected $configurator;

    /**
     * @var Configurator[]
     */
    private $configurators;

    protected function setUp()
    {
        $this->setUpContainer();
    }

    /**
     * Initializes the generator instance.
     *
     * This method must be called *after* setting-up the prophesy expectations.
     * As this will load the container services.
     */
    final protected function initConfigurator()
    {
        if (null !== $this->configurator) {
            return;
        }

        $this->configurator = $this->container['class_initializer']->getNewInstance($this->getConfiguratorClass());

        $configuratorLoader = $this->container->getConfiguratorsLoaderService();
        $configuratorLoader->addConfigurator($this->configurator);

        $this->configurators = $configuratorLoader->getConfigurators();
    }

    /**
     * @return string
     */
    protected function getConfiguratorClass()
    {
        $class = substr(str_replace('\\Tests\\', '\\', get_class($this)), 0, -4);

        if (class_exists($class)) {
            return $class;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unable to automatically guess the Configurator className for "%s", overwrite the getConfiguratorClass() method.',
                get_class($this)
            )
        );
    }

    /**
     * Runs the Configurator (and it's parents).
     *
     * This method will also load all the related configurators and ensures:
     * - there are no conflicts between the loaded configurators
     * - values are validated/transformed
     * - no duplicate answers are provided
     *
     * @param array $values
     *
     * @return array The finalized answers.
     */
    protected function runConfigurator(array $values)
    {
        $this->initConfigurator();

        $questioner = new UsingDefaultsQuestioner();
        $configuration = $questioner->interact($this->configurators, true, $values)->getValues();

        foreach ($this->configurators as $finalizer) {
            $finalizer->finalizeConfiguration($configuration);
        }

        return array_merge_recursive($configuration);
    }

    protected static function assertArrayHasKeyAndValueEquals($key, array $array, $value)
    {
        self::assertArrayHasKey($key, $array);
        self::assertEquals($value, $array[$key]);
    }

    protected static function assertArrayHasKeyAndArrayValuesEquals($key, array $array, $value)
    {
        self::assertArrayHasKey($key, $array);
        self::assertEquals($value, array_merge([], $array[$key]));
    }
}
