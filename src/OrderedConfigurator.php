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

namespace Rollerworks\Tools\SkeletonDancer;

interface OrderedConfigurator extends Configurator
{
    /**
     * Returns the order of this configurator.
     *
     * It ensures that this configurator can check and use
     * the already resolved questions (rather then being dependent on them).
     *
     * @return int A value between -10 and 10 (lower will placed earlier in the list)
     */
    public function getOrder(): int;
}
