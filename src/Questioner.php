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

interface Questioner
{
    /**
     * Interacts with the QuestionsSet to set answers.
     *
     * @param QuestionsSet $questions
     */
    public function interact(QuestionsSet $questions);
}
