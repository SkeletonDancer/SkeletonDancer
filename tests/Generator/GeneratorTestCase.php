<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Tests\Generator;

use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Questioner\UsingDefaultsQuestioner;
use Rollerworks\Tools\SkeletonDancer\Tests\ContainerCreator;

/**
 * Acceptance test-case for a single Generator.
 */
abstract class GeneratorTestCase extends \PHPUnit_Framework_TestCase
{
    use ContainerCreator;

    /**
     * @var Generator
     */
    protected $generator;

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
    final protected function initGenerator()
    {
        if (null !== $this->generator) {
            return;
        }

        $this->generator = $this->container['class_initializer']->getNewInstance($this->getGeneratorClass());

        $configuratorLoader = $this->container->getConfiguratorsLoaderService();
        $configuratorLoader->loadFromGenerator($this->generator);

        $this->configurators = $configuratorLoader->getConfigurators();
    }

    /**
     * @return string
     */
    protected function getGeneratorClass()
    {
        $class = substr(str_replace('\\Tests\\', '\\', get_class($this)), 0, -4);

        if (class_exists($class)) {
            return $class;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unable to automatically guess the generator className for "%s", overwrite the getGeneratorClass() method.',
                get_class($this)
            )
        );
    }

    /**
     * Runs the generator.
     *
     * This method will also load all the related configurators and ensures:
     * - there are no conflicts between the loaded configurators
     * - values are validated/transformed
     * - no duplicate answers are provided
     *
     * @param array $values
     *
     * @return int The status of the execution (0=ok, 1=skipped)
     */
    protected function runGenerator(array $values, array $extraValues = [])
    {
        $this->initGenerator();

        $questioner = new UsingDefaultsQuestioner();
        $configuration = $questioner->interact($this->configurators, true, $values)->getValues();

        foreach ($this->configurators as $finalizer) {
            $finalizer->finalizeConfiguration($configuration);
        }

        $result = $this->generator->generate(array_merge_recursive($configuration, $extraValues));

        return null === $result ? 0 : $result;
    }
}
