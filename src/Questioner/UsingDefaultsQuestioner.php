<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Questioner;

use Rollerworks\Tools\SkeletonDancer\Questioner;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Symfony\Component\Console\Question\Question;

final class UsingDefaultsQuestioner implements Questioner
{
    private $currentConfigurator;
    private $currentQuestion;

    /**
     * {@inheritdoc}
     */
    public function interact(array $configurators, $skipOptional = true, array $defaults = [])
    {
        $questionCommunicator = function (Question $question, $name) {
            $this->currentQuestion = $name;
            $value = $question->getDefault();

            if ($validator = $question->getValidator()) {
                $value = $validator($value);
            }

            return $value;
        };

        $questions = new QuestionsSet($questionCommunicator, $defaults, false);

        try {
            foreach ($configurators as $configurator) {
                $this->currentConfigurator = get_class($configurator);
                $configurator->interact($questions);
            }

            $this->currentQuestion = null;
            $this->currentConfigurator = null;

            return $questions;
        } catch (\Exception $e) {
            if (null !== $this->currentQuestion) {
                throw new \RuntimeException(
                    sprintf(
                        'An exception was thrown during the processing of question "%s", defined in "%s", message: %s',
                        $this->currentQuestion,
                        $this->currentConfigurator,
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
