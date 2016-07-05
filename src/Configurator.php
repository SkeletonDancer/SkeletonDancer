<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer;

interface Configurator
{
    /**
     * Interacts with the QuestionsSet to set answers.
     *
     * @param QuestionsSet $questions
     */
    public function interact(QuestionsSet $questions);

    /**
     * Allows to modify the configuration, after all
     * configurators are interacted with.
     *
     * @param array $configuration
     */
    public function finalizeConfiguration(array &$configuration);
}
