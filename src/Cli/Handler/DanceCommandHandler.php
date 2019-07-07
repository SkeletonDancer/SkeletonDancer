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

namespace SkeletonDancer\Cli\Handler;

use SkeletonDancer\ClassInitializer;
use SkeletonDancer\Configuration\DanceSelector;
use SkeletonDancer\InteractiveQuestionInteractor;
use SkeletonDancer\Runner\VerboseRunner;
use SkeletonDancer\Service\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;

final class DanceCommandHandler
{
    private $style;
    private $filesystem;
    private $danceSelector;
    private $classInitializer;

    public function __construct(
        SymfonyStyle $style,
        Filesystem $filesystem,
        DanceSelector $danceSelector,
        ClassInitializer $classInitializer
    ) {
        $this->style = $style;
        $this->filesystem = $filesystem;
        $this->danceSelector = $danceSelector;
        $this->classInitializer = $classInitializer;
    }

    public function handle(Args $args, IO $io)
    {
        $this->style->title('SkeletonDancer');

        $dance = $this->danceSelector->resolve($args->getArgument('name'));
        $questioner = new InteractiveQuestionInteractor($this->style, $io, $this->classInitializer);

        $this->style->text(sprintf('Using dance: %s (%s)', $dance->name, $dance->directory));
        (new VerboseRunner($this->style, $this->classInitializer))->run($dance, $questioner->interact($dance, !$args->getOption('all')));
    }
}
