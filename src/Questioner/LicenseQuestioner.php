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

namespace SkeletonDancer\Questioner;

use SkeletonDancer\Question;
use SkeletonDancer\Questioner;
use SkeletonDancer\QuestionsSet;

final class LicenseQuestioner implements Questioner
{
    const LICENSES = [
        'MIT',
        'BSD-2-Clause',
        'BSD-3-Clause',
        'BSD-4-Clause',
        'MPL-2.0',
        'GPL-2.0',
        'GPL-2.0+',
        'GPL-3.0',
        'GPL-3.0+',
        'LGPL-2.1',
        'LGPL-2.1+',
        'LGPL-3.0',
        'LGPL-3.0+',
        'Apache-2.0',
        'Proprietary',
    ];

    public function interact(QuestionsSet $questions)
    {
        $questions->communicate(
            'license',
            Question::choice('License', self::LICENSES, 'MIT')->setHelp('Labeled with "+" means higher inclusive')
        );
    }
}
