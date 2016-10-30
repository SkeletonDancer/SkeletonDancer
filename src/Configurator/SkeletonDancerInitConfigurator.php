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

namespace Rollerworks\Tools\SkeletonDancer\Configurator;

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Question;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;

final class SkeletonDancerInitConfigurator implements Configurator
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function interact(QuestionsSet $questions)
    {
        $questions->communicate(
            'profiles',
            Question::multiChoice('Profiles to enable (dumps all questions for ease of use)', array_keys($this->config->getProfiles()))
        );
    }

    public function finalizeConfiguration(array &$configuration)
    {
    }
}
