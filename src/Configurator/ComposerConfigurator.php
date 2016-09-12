<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Configurator;

use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Question;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;

final class ComposerConfigurator implements Configurator
{
    public function interact(QuestionsSet $questions)
    {
        $questions->communicate('composer_type', Question::ask('Composer package type', 'library'));

        if ('stable' !== $questions->communicate(
                'composer_minimum_stability',
                Question::choice(
                    'Minimum stability',
                    ['dev', 'alpha', 'beta', 'RC', 'stable'],
                    'stable'
                )->markOptional()
            )
        ) {
            $questions->communicate(
                'composer_prefer_stable',
                Question::confirm(
                    'Prefer stable?',
                    true
                )->markOptional()
            );
        } else {
            $questions->set('composer_prefer_stable', false);
        }
    }

    public function finalizeConfiguration(array &$configuration)
    {
        if ('stable' !== $configuration['composer_minimum_stability']) {
            $configuration['composer']['minimum-stability'] = $configuration['composer_minimum_stability'];

            if ($configuration['composer_prefer_stable']) {
                $configuration['composer']['prefer-stable'] = true;
            }
        }

        $configuration['composer']['type'] = $configuration['composer_type'];
    }
}
