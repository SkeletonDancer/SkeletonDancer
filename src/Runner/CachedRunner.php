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

final class CachedRunner implements Runner
{
    const CACHE_FILENAME = '.dancer-defaults.json';

    private $style;
    private $config;
    private $runner;

    public function __construct(SymfonyStyle $style, Config $config, Runner $runner)
    {
        $this->style = $style;
        $this->config = $config;
        $this->runner = $runner;
    }

    public function skipOptional(): bool
    {
        return $this->runner->skipOptional();
    }

    public function getCachedDefaults(ResolvedProfile $profile)
    {
        if (!file_exists($file = $this->config->get('current_dir').'/'.self::CACHE_FILENAME)) {
            return [];
        }

        $config = json_decode(file_get_contents($file), true, 512, JSON_BIGINT_AS_STRING);
        $this->removeCache(); // Remove the cache after reading so the user can abort and ignore the defaults.

        if (!is_array($config) || !isset($config['profile'], $config['defaults'], $config['skip_option'])) {
            $this->style->warning('Cached defaults file is corrupted, ignoring.');

            return [];
        }

        if ($config['profile'] !== $profile->name || $config['skip_option'] !== $this->runner->skipOptional()) {
            $this->style->note('Cached defaults configuration mismatch, ignoring.');

            return [];
        }

        $this->style->note(
            [
                'It seems the last execution in this directory did not complete successfully.',
                'Using cached defaults for the questioner defaults.',
                'When you provide other answers, some auto guessed values may not work as expected!',
                'Abort (ctrl + c) if you don\'t want to reuse the default values.',
            ]
        );

        return $config['defaults'];
    }

    public function run(ResolvedProfile $profile, array $generators, QuestionsSet $answers)
    {
        $this->storeCache($profile, $answers);

        $this->runner->run($profile, $generators, $answers);

        $this->removeCache();
    }

    private function storeCache(ResolvedProfile $profile, QuestionsSet $questionsSet)
    {
        file_put_contents(
            $this->config->get('current_dir').'/'.self::CACHE_FILENAME,
            json_encode(
                [
                    'profile' => $profile->name,
                    'defaults' => $questionsSet->getAnswers(),
                    'skip_option' => $this->runner->skipOptional(),
                ],
                JSON_PRETTY_PRINT
            )
        );
    }

    private function removeCache()
    {
        unlink($this->config->get('current_dir').'/'.self::CACHE_FILENAME);
    }
}
