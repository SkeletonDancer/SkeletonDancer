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

interface DependentGenerator extends Configurator
{
    /**
     * Returns which other generators this generator depends on.
     *
     * It ensures that this generator can use the already
     * generated structure from other generator.
     *
     * @return string[] Returns an array with FQCN's
     */
    public function getDependencies(): array;
}
