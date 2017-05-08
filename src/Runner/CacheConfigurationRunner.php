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
use SkeletonDancer\Service\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CacheConfigurationRunner implements Runner
{
    const CACHE_FILENAME = 'dancer-config.json';

    private $style;
    private $runner;
    private $filesystem;

    public function __construct(SymfonyStyle $style, Filesystem $filesystem, Runner $runner)
    {
        $this->style = $style;
        $this->filesystem = $filesystem;
        $this->runner = $runner;
    }

    public function run(Dance $dance, QuestionsSet $answers)
    {
        try {
            $this->runner->run($dance, $answers);
        } catch (\Exception $e) {
            $this->storeCache($answers);

            $this->style->error(
                [
                    'Oh no. Something went wrong during the generation process!',
                    'Your answers were stored, use the --import='.self::CACHE_FILENAME.' option to continue.',
                ]
            );

            throw $e;
        }
    }

    private function storeCache(QuestionsSet $questionsSet): void
    {
        file_put_contents(
            $this->filesystem->getCurrentDir().'/'.self::CACHE_FILENAME,
            json_encode($questionsSet->getAnswers(), JSON_PRETTY_PRINT)
        );
    }
}
