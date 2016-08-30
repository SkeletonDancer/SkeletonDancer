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

interface Generator
{
    /**
     * Generates the file within the projects directory.
     *
     * @param array $configuration The resolved configuration for the
     *                             generator
     */
    public function generate(array $configuration);

    /**
     * Returns a list of configurators that this generator depends on.
     *
     * Note. Only the configurators "THIS" generator depends on
     * must be listed. Parent configurators are already loaded.
     *
     * The complete list of configurators is merged and sorted
     * in the correct order later.
     *
     * @return string[]
     */
    public function getConfigurators();
}
