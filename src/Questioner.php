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

interface Questioner
{
    /**
     * @param Configurator[] $configurators
     * @param bool           $skipOptional
     * @param array          $defaults
     *
     * @return array
     */
    public function interact(array $configurators, $skipOptional = true, array $defaults = []);
}
