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

use SkeletonDancer\ClassInitializer;
use SkeletonDancer\Dance;
use SkeletonDancer\Question;
use SkeletonDancer\Questioner;
use SkeletonDancer\QuestionInteractor;
use SkeletonDancer\QuestionsSet;

final class UsingDefaultsProvidedQuestionInteractor implements QuestionInteractor
{
    private $classInitializer;
    private $answers;
    private $currentQuestion;

    public function __construct(ClassInitializer $classInitializer, array $answers)
    {
        $this->classInitializer = $classInitializer;
        $this->answers = $answers;
    }

    public function interact(Dance $dance, bool $skipOptional = true): QuestionsSet
    {
        $questionCommunicator = function (Question $question, $name) {
            $this->currentQuestion = $name;
            $value = \array_key_exists($name, $this->answers) ? $this->answers[$name] : $question->getDefault();

            if ($validator = $question->getValidator()) {
                $value = $validator($value);
            }

            if ($normalizer = $question->getNormalizer()) {
                $value = $normalizer($value);
            }

            return $value;
        };

        $questions = new QuestionsSet($questionCommunicator, false);

        try {
            /** @var Questioner $questioner */
            $questioner = $this->classInitializer->getNewInstance(current($dance->questioners), Questioner::class);
            $questioner->interact($questions);

            return $questions;
        } catch (\Exception $e) {
            if (null !== $this->currentQuestion) {
                throw new \RuntimeException(
                    sprintf(
                        'An exception was thrown during the processing of question "%s", defined in "%s", message: %s',
                        $this->currentQuestion,
                        current($dance->questioners),
                        $e->getMessage()
                    ),
                    0,
                    $e
                );
            }

            throw $e;
        }
    }
}
