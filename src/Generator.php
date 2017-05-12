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

namespace SkeletonDancer;

interface Generator
{
    public const STATUS_SUCCESS = 0;
    public const STATUS_SKIPPED = 1;
    public const STATUS_FAILURE = 2;

    /**
     * Generates the file within the projects directory.
     *
     * @param array $configuration The resolved configuration for the
     *                             generator
     */
    public function generate(array $configuration);
}
