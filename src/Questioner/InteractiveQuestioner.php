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

namespace Rollerworks\Tools\SkeletonDancer\Questioner;

use Rollerworks\Tools\SkeletonDancer\Questioner;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Symfony\Component\Console\Style\SymfonyStyle;

final class InteractiveQuestioner implements Questioner
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var callable
     */
    private $answerSetFactory;

    public function __construct(SymfonyStyle $io, callable $answerSetFactory)
    {
        $this->io = $io;
        $this->answerSetFactory = $answerSetFactory;
    }

    public function interact(array $configurators, $skipOptional = true, array $variables = [], array $defaults = []): QuestionsSet
    {
        $questionCommunicator = function ($question) {
            return $this->io->askQuestion($question);
        };

        $answersSet = call_user_func($this->answerSetFactory, $variables, $defaults);
        $questions = new QuestionsSet($questionCommunicator, $answersSet, $skipOptional);

        foreach ($configurators as $configurator) {
            $configurator->interact($questions);
        }

        return $questions;
    }
}
