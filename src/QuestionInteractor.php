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

interface QuestionInteractor
{
    /**
     * @param Dance $dance
     * @param bool  $skipOptional
     *
     * @return QuestionsSet
     */
    public function interact(Dance $dance, bool $skipOptional = true): QuestionsSet;
}
