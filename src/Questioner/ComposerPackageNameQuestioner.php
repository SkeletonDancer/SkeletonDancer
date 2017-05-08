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
use SkeletonDancer\StringUtil;

class ComposerPackageNameQuestioner implements Questioner
{
    public function interact(QuestionsSet $questions)
    {
        $questions->communicate(
            'package_name',
            Question::ask(
                'Package name (<vendor>/<name>)',
                function (array $config) {
                    return $this->getDefaultValue($config);
                },
                function ($name) {
                    return $this->validateName($name);
                }
            )
        );
    }

    protected function getDefaultValue(array $config): ?string
    {
        if (!empty($config['project_name']) &&
            preg_match('/^(?P<vendor>[a-z0-9_.-]+)\s+(?P<name>[a-z0-9_.-]+)$/i', $config['project_name'], $regs)
        ) {
            return mb_strtolower(StringUtil::humanize($regs[1]).'/'.StringUtil::humanize($regs[2]));
        }

        return null;
    }

    protected function validateName($name): ?string
    {
        if (!preg_match('{^[a-z0-9_.-]+/[a-z0-9_.-]+$}', (string) $name)) {
            throw new \InvalidArgumentException(
                'The package name '.
                $name.
                ' is invalid, it should be lowercase and have a vendor name, a forward slash, and a package name, matching: [a-z0-9_.-]+/[a-z0-9_.-]+'
            );
        }

        return $name;
    }
}
