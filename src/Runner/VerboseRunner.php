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
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Rollerworks\Tools\SkeletonDancer\ResolvedProfile;
use Rollerworks\Tools\SkeletonDancer\Runner;
use Symfony\Component\Console\Style\SymfonyStyle;

final class VerboseRunner implements Runner
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
        $configuration = $answers->getFinalizedValues($profile->configurators);
        $startTime = microtime(true);

        $this->style->text(
            ['', '<fg=green>Start dancing, this may take a while...</>', sprintf('Total of tasks: %d', $total), '']
        );

        foreach ($generators as $generator) {
            $this->runGenerator($generator, $configuration, $i, $total);
            ++$i;
        }

        if ($this->style->isVerbose()) {
            $this->style->text([
                '',
                sprintf(
                    '// Total time: %s, Memory: %4.2fMB',
                    self::secondsToTimeString(microtime(true) - $startTime),
                    memory_get_peak_usage(true) / 1048576
                ),
            ]);
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
                    sprintf('// Dancer config directory: %s', $this->config->get('dancer_directory', '')),
                    sprintf('// Project directory: %s', $this->config->get('project_directory', '')),
                ]
            );
        }
    }

    private function runGenerator(Generator $generator, array $configuration, int $i, int $total)
    {
        $this->style->write(sprintf(' [%d/%d] Running %s', $i, $total, get_class($generator)));
        $startTime = microtime(true);

        $status = $generator->generate($configuration) ?? 0;
        $statuses = [
            0 => '<fg=green>OK...</>',
            1 => '<fg=blue>SKIPPED...</>',
            2 => '<fg=red>ERROR...</>',
        ];

        $this->style->write('  '.$statuses[$status]);

        if ($this->style->isVerbose()) {
            $this->style->write(sprintf(' (%s)', self::secondsToTimeString(microtime(true) - $startTime)));
        }

        $this->style->writeln('');
    }

    /**
     * Formats the elapsed time as a string.
     *
     * @link https://github.com/sebastianbergmann/php-timer/
     *
     * @param float $time
     *
     * @return string
     */
    private static function secondsToTimeString(float $time)
    {
        $ms = round($time * 1000);

        foreach (['hour' => 3600000, 'minute' => 60000, 'second' => 1000] as $unit => $value) {
            if ($ms >= $value) {
                $time = floor($ms / $value * 100.0) / 100.0;

                return $time.' '.($time == 1 ? $unit : $unit.'s');
            }
        }

        return $ms.' ms';
    }
}
