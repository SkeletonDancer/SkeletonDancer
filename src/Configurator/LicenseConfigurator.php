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

final class LicenseConfigurator implements Configurator
{
    public function interact(QuestionsSet $questions)
    {
        if ('MPL-2.0' === $questions->communicate(
                'license',
                Question::choice('License', self::getLicenses(), 'MIT')->setHelp(
                    'Labeled with "+" means higher inclusive'
                )
            )
        ) {
            $questions->communicate(
                'license_secondary_incompatibility',
                Question::confirm('Incompatible With Secondary Licenses?', false)
            );
        } else {
            $questions->set('license_secondary_incompatibility', false);
        }
    }

    public static function getLicenses()
    {
        return [
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
    }

    public function finalizeConfiguration(array &$configuration)
    {
        if (strcasecmp('proprietary', $configuration['license']) === 0) {
            $configuration['license'] = 'proprietary';
        }
    }
}
