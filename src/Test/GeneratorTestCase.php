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

namespace SkeletonDancer\Test;

use PHPUnit\Framework\TestCase;
use SkeletonDancer\Generator;

/**
 * Acceptance test-case for a single Generator.
 */
abstract class GeneratorTestCase extends TestCase
{
    use ContainerCreator;

    protected function setUp()
    {
        $this->setUpContainer();
    }

    protected function getGeneratorClass(): string
    {
        $class = mb_substr(str_replace('\\Tests\\', '\\', \get_class($this)), 0, -4);

        if (class_exists($class)) {
            return $class;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unable to automatically guess the generator className for "%s", overwrite the getGeneratorClass() method.',
                \get_class($this)
            )
        );
    }

    /**
     * Runs the Generator.
     *
     * The Generator is determined using getGeneratorClass().
     * This method will initialize the Generator (with autowired services).
     *
     * @param array $answers
     *
     * @return int The status of the execution (0=ok, 1=skipped, 2=failure)
     */
    protected function runGenerator(array $answers = []): int
    {
        /** @var Generator $generator */
        $generator = $this->container['class_initializer']->getNewInstance($this->getGeneratorClass());

        return $generator->generate($answers) ?? 0;
    }
}
