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

use SkeletonDancer\Dance;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
final class DanceSelector
{
    private $dancesProvider;
    private $style;

    public function __construct(DancesProvider $dancesProvider, SymfonyStyle $style)
    {
        $this->style = $style;
        $this->dancesProvider = $dancesProvider;
    }

    public function resolve(bool $ignoreLocal, ?string $selected = null): Dance
    {
        $dances = $ignoreLocal ? $this->dancesProvider->global() : $this->dancesProvider->all();

        if (null !== $selected) {
            return $dances->get($selected);
        }

        return $dances->get($this->style->choice('Dance', $dances->names()));
    }
}
