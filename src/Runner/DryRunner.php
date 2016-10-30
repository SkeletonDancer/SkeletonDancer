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

namespace Rollerworks\Tools\SkeletonDancer\Runner;

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Rollerworks\Tools\SkeletonDancer\ResolvedProfile;
use Rollerworks\Tools\SkeletonDancer\Runner;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DryRunner implements Runner
{
    private $style;
    private $config;
    private $skipOptional;

    public function __construct(
        SymfonyStyle $style,
        Config $config,
        bool $skipOptional = true
    ) {
        $this->style = $style;
        $this->config = $config;
        $this->skipOptional = $skipOptional;
    }

    public function skipOptional(): bool
    {
        return $this->skipOptional;
    }

    public function run(ResolvedProfile $profile, array $generators, QuestionsSet $answers)
    {
        $this->reportHeader($profile);

        $i = 1;
        $total = count($generators);

        $this->style->text(
            [
                '',
                '<fg=green>Start dancing, this may take a while...</>',
                sprintf('Total of tasks: %d', $total),
                '<comment>Dry-run operation, no actual files will be generated.</>',
            ]
        );

        foreach ($generators as $generator) {
            $this->style->writeln(sprintf(' [%d/%d] Running %s', $i, $total, get_class($generator)));
            ++$i;
        }

        $this->style->success('Done!');
    }

    private function reportHeader(ResolvedProfile $profile)
    {
        $this->style->text(sprintf('Using profile: %s', $profile->name));

        if ($this->style->isVerbose()) {
            $this->style->text(
                [
                    sprintf('// Using config file: %s', $this->config->get('config_file', '')),
                    sprintf('// Project directory: %s', $this->config->get('project_directory', '')),
                    sprintf('// Dancer config directory: %s', $this->config->get('dancer_directory', '')),
                    '',
                ]
            );
        }
    }
}
