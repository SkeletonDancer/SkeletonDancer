<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer;

interface DependentConfigurator extends Configurator
{
    /**
     * Returns which other configurators this configurator depends on.
     *
     * It ensures that this configurator can use the already resolved
     * questions from other Configurators.
     *
     * @return string[] Returns an array with FQCN's
     */
    public function getDependencies();
}
