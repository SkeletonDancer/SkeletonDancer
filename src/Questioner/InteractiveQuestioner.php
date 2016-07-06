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
use Symfony\Component\Console\Style\SymfonyStyle;

final class InteractiveQuestioner implements Questioner
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * {@inheritdoc}
     */
    public function interact(array $configurators, $skipOptional = true, array $defaults = [])
    {
        $questionCommunicator = function ($question) {
            return $this->io->askQuestion($question);
        };

        $questions = new QuestionsSet($questionCommunicator, $defaults, $skipOptional);

        foreach ($configurators as $configurator) {
            $configurator->interact($questions);
        }

        return $questions;
    }
}
