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

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyStyleTestHelper extends SymfonyStyle
{
    public function askQuestion(Question $question)
    {
        $question->setMaxAttempts(1);

        return parent::askQuestion($question);
    }
}
