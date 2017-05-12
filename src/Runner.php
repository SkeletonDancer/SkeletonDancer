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

/**
 * A Runner executes the.
 */
interface Runner
{
    /**
     * Execute the runner with the dance.
     *
     * @param Dance        $dance
     * @param QuestionsSet $answers
     */
    public function run(Dance $dance, QuestionsSet $answers);
}
