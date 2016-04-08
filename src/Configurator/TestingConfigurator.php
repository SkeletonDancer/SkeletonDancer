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
use Rollerworks\Tools\SkeletonDancer\StringUtil;

final class TestingConfigurator implements Configurator
{
    public function interact(QuestionsSet $questions)
    {
        $questions->communicate('enable_phpunit', Question::confirm('Enable PHPUnit?', true));

        if ($questions->communicate('enable_phpspec', Question::confirm('Enable PHPSpec?', false))) {
            $questions->communicate(
                'phpspec_suite_name',
                Question::confirm(
                    'PHPSpec suite-name',
                    function (array $values) {
                        return isset($values['name']) ? StringUtil::shortProductName($values['name']) : null;
                    }
                )
            );
        } else {
            $questions->communicate('phpspec_shortname', null);
        }

        if ($questions->communicate('enable_behat', Question::confirm('Enable Behat?', false))) {
            $questions->communicate(
                'behat_suite_name',
                Question::confirm(
                    'Behat suite-name',
                    function (array $values) {
                        return isset($values['name']) ? StringUtil::shortProductName($values['name']) : null;
                    }
                )
            );
        } else {
            $questions->set('behat_shortname', null);
        }

        $questions->communicate('enable_mink', Question::confirm('Enable Mink?', false));
    }

    public function finalizeConfiguration(array &$configuration)
    {
        if ($configuration['enable_phpunit']) {
            $configuration['composer']['autoload-dev']['psr-4'][$configuration['namespace'].'\\Tests\\'] = 'tests/';
            $configuration['git']['ignore'][] = 'phpunit.xml';
            $configuration['git']['export-ignore'][] = 'phpunit.xml.dist';
            $configuration['git']['export-ignore'][] = 'tests/';
        }

        if ($configuration['enable_phpspec']) {
            $configuration['git']['export-ignore'][] = 'phpspec.yml';
            $configuration['git']['export-ignore'][] = 'spec/';

            $configuration['composer']['require-dev'][] = 'phpspec/phpspec';
        }

        if ($configuration['enable_behat']) {
            $configuration['git']['export-ignore'][] = 'behat.yml.dist';
            $configuration['git']['export-ignore'][] = 'features/';

            $configuration['composer']['require-dev'][] = 'behat/behat';
        }

        if ($configuration['enable_mink']) {
            $configuration['composer']['require-dev'][] = 'behat/mink';
            $configuration['composer']['require-dev'][] = 'behat/mink-extension';
            $configuration['composer']['require-dev'][] = 'behat/mink-browserkit-driver';
            $configuration['composer']['require-dev'][] = 'behat/mink-selenium2-driver';
            $configuration['composer']['require-dev'][] = 'lakion/mink-debug-extension';
        }

        return $configuration;
    }
}
