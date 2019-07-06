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

namespace SkeletonDancer\Runner;

use SkeletonDancer\Dance;
use SkeletonDancer\QuestionsSet;
use SkeletonDancer\Runner;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DryRunner implements Runner
{
    private $style;

    public function __construct(SymfonyStyle $style)
    {
        $this->style = $style;
    }

    public function run(Dance $dance, QuestionsSet $answers)
    {
        $i = 1;
        $total = \count($dance->generators);

        $this->style->text(
            [
                '',
                '<fg=green>Start your dance practice, this wont take long...</>',
                sprintf('Total of tasks: %d', $total),
                '<comment>Dry-run operation, no actual files will be generated.</>',
            ]
        );

        foreach ($dance->generators as $generator) {
            $this->style->writeln(sprintf(' [%d/%d] Running %s', $i, $total, $generator));
            ++$i;
        }

        $this->style->success('Done!');
    }
}
