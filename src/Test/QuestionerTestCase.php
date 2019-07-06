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
use SkeletonDancer\Dance;
use SkeletonDancer\QuestionInteractor;

/**
 * Acceptance test-case for a Questioner.
 */
abstract class QuestionerTestCase extends TestCase
{
    use ContainerCreator;

    /**
     * @var QuestionInteractor
     */
    protected $questioner;

    protected function setUp()
    {
        $this->setUpContainer();
    }

    protected function getQuestionerClass(): string
    {
        $class = mb_substr(str_replace('\\Tests\\', '\\', \get_class($this)), 0, -4);

        if (class_exists($class)) {
            return $class;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Unable to automatically guess the Questioner className for "%s", overwrite the getQuestionerClass() method.',
                \get_class($this)
            )
        );
    }

    /**
     * Runs the Questioner.
     *
     * The Questioner is determined using getQuestionerClass().
     *
     * This method will initialize the Questioner (with autowired services)
     * and and ensures values are validated, transformed, and no duplicates
     * are provided.
     *
     * @param array $answers
     *
     * @return array The final answers
     */
    protected function runQuestioner(array $answers): array
    {
        $dance = new Dance('test/test', '', [$this->getQuestionerClass()], []);
        $interactor = new UsingDefaultsProvidedQuestionInteractor($this->container['class_initializer'], $answers);

        return $interactor->interact($dance, true)->getAnswers();
    }
}
