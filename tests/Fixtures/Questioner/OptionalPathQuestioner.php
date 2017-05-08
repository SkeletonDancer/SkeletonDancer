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

namespace SkeletonDancer\Tests\Fixtures\Questioner;

use SkeletonDancer\Question;
use SkeletonDancer\Questioner;
use SkeletonDancer\QuestionsSet;

class OptionalPathQuestioner implements Questioner
{
    public function interact(QuestionsSet $questions)
    {
        $questions->communicate('path', Question::ask('Path', '/')->markOptional()->setMaxAttempts(1));
    }
}
