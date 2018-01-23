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

namespace SkeletonDancer\Configuration;

use SkeletonDancer\Container;
use SkeletonDancer\Dance;
use SkeletonDancer\Dances;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DanceSelector
{
    private $dances;
    private $style;
    private $container;

    public function __construct(Dances $dances, SymfonyStyle $style, Container $container)
    {
        $this->dances = $dances;
        $this->style = $style;
        $this->container = $container;
    }

    public function resolve(string $dance = null): Dance
    {
        $selectedDance = $dance; // Preserve the original input for the exception.

        if (null !== $dance && !$this->dances->has($dance)) {
            $this->style->error(sprintf('Dance "%s" is not installed.', $dance));
            $selectedDance = null;
        }

        $dances = $this->dances->all();

        if (!count($dances)) {
            throw new \InvalidArgumentException('Oh no there are no dances! Please install a dance before you continue');
        }

        if (!$selectedDance && $this->container['sf.console_input']->isInteractive()) {
            $selectedDance = $this->style->choice('Dance', $dances = array_keys($dances));
        }

        if (!$selectedDance) {
            throw new \InvalidArgumentException(
                (null === $dance ? 'No Dance selected. ' : 'Dance "'.$dance.'" is not installed. ').
                'Installed: '.
                implode(', ', array_keys($dances))
            );
        }

        return $this->container['dance'] = $this->dances->get($selectedDance);
    }
}
