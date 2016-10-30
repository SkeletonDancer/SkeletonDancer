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

use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Question;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Rollerworks\Tools\SkeletonDancer\StringUtil;

final class DocumentationConfigurator implements Configurator
{
    public function interact(QuestionsSet $questions)
    {
        $format = $questions->communicate(
            'doc_format',
            Question::choice('Documentation format', ['rst', 'markdown', 'none'])
        );

        if ('rst' === $format) {
            $questions->communicate(
                'rst_short_name',
                Question::ask(
                    'Short product name',
                    function (array $config) {
                        return StringUtil::shortProductName($config['name']);
                    }
                )
            );
        }
    }

    public function finalizeConfiguration(array &$configuration)
    {
    }
}
