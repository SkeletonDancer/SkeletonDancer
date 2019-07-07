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

use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\IO\IO;

final class InteractiveQuestionInteractor implements QuestionInteractor
{
    private $style;
    private $classInitializer;

    /**
     * @var IO
     */
    private $io;

    public function __construct(SymfonyStyle $style, IO $io, ClassInitializer $classInitializer)
    {
        $this->style = $style;
        $this->io = $io;
        $this->classInitializer = $classInitializer;
    }

    public function interact(Dance $dance, bool $skipOptional = true): QuestionsSet
    {
        if (!$this->io->isInteractive()) {
            throw new \RuntimeException('Sorry. But this command can only be run in interactive mode.');
        }

        $questionCommunicator = function ($question) {
            return $this->style->askQuestion($question);
        };

        $questions = new QuestionsSet($questionCommunicator, $skipOptional);

        foreach ($dance->questioners as $configurator) {
            $this->classInitializer->getNewInstanceFor($dance, $configurator, Questioner::class)->interact($questions);
        }

        return $questions;
    }
}
