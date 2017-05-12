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

interface Hosting
{
    /**
     * Returns whether this hosting adapter (provides) supports the given dance
     * and version.
     *
     * @param string $name
     * @param string $version
     * @param string $message
     *
     * @return bool
     */
    public function supports(string $name, ?string $version, &$message): bool;

    /**
     * Gets the Git repository URL of the dance.
     *
     * @param string $name
     *
     * @return string
     */
    public function getRepositoryUrl(string $name): string;

    /**
     * Gets the web URL of the dance, for further information.
     *
     * @param string $name
     *
     * @return string
     */
    public function getWebUrl(string $name): string;
}
