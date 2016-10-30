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

namespace Rollerworks\Tools\SkeletonDancer\Cli\Handler;

use Rollerworks\Tools\SkeletonDancer\Configuration\AnswersSetFactory;
use Rollerworks\Tools\SkeletonDancer\Configuration\AutomaticProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\InteractiveProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\ProfileConfigResolver;
use Rollerworks\Tools\SkeletonDancer\Questioner\InteractiveQuestioner;
use Rollerworks\Tools\SkeletonDancer\Questioner\UsingDefaultsQuestioner;
use Rollerworks\Tools\SkeletonDancer\Runner\CachedRunner;
use Rollerworks\Tools\SkeletonDancer\Runner\DryRunner;
use Rollerworks\Tools\SkeletonDancer\Runner\VerboseRunner;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;

final class GenerateCommandHandler
{
    private $style;
    private $config;
    private $answerSetFactory;
    private $profileConfigResolver;

    public function __construct(
        SymfonyStyle $style,
        Config $config,
        ProfileConfigResolver $profileConfigResolver,
        AnswersSetFactory $answerSetFactory
    ) {
        $this->style = $style;
        $this->config = $config;
        $this->profileConfigResolver = $profileConfigResolver;
        $this->answerSetFactory = $answerSetFactory;
    }

    public function handle(Args $args, IO $io)
    {
        $this->style->title('SkeletonDancer');

        $interactive = $io->isInteractive();
        $cachedDefaults = [];

        $questioner = $this->createQuestioner($interactive);
        $profile = $this->getResolvedProfile($args, $io);
        $runner = $this->createRunner($args);

        if ($interactive) {
            $runner = new CachedRunner($this->style, $this->config, $runner);
            $cachedDefaults = $runner->getCachedDefaults($profile);
        }

        $profile->execute($questioner, $runner, $cachedDefaults);
    }

    private function getResolvedProfile(Args $args, IO $io)
    {
        $profileResolver = new AutomaticProfileResolver($this->config);

        if ($io->isInteractive()) {
            $profileResolver = new InteractiveProfileResolver(
                $this->config,
                $this->style,
                $profileResolver
            );
        }

        return $this->profileConfigResolver->resolve(
            $profileResolver->resolve($args->getArgument('profile'))->name
        );
    }

    private function createRunner(Args $args)
    {
        if ($args->getOption('dry-run')) {
            return new DryRunner($this->style, $this->config, !$args->getOption('all'));
        }

        return new VerboseRunner($this->style, $this->config, !$args->getOption('all'));
    }

    private function createQuestioner(bool $interactive)
    {
        if ($interactive) {
            return new InteractiveQuestioner($this->style, [$this->answerSetFactory, 'create']);
        }

        return new UsingDefaultsQuestioner([$this->answerSetFactory, 'create']);
    }
}
