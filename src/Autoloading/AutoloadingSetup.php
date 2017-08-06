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

namespace SkeletonDancer\Autoloading;

use SkeletonDancer\Container;
use SkeletonDancer\Dance;

final class AutoloadingSetup
{
    /**
     * @var Container
     */
    public $container;

    /**
     * @var Psr4ClassLoader
     */
    private $classLoader;

    public function __construct(Psr4ClassLoader $classLoader, Container $container)
    {
        $this->classLoader = $classLoader;
        $this->container = $container;
    }

    public function setUpFor(Dance $dance)
    {
        $danceDirectory = $dance->directory.'/';

        // Prefix directory with the dance-directory.
        $dirPrefixer = function (string $dir) use ($danceDirectory) {
            return $danceDirectory.trim($dir, '\\/');
        };

        foreach ($dance->autoloading['psr-4'] ?? [] as $prefix => $dir) {
            $this->classLoader->addPrefix($prefix, $dirPrefixer($dir));
        }

        foreach ($dance->autoloading['files'] ?? [] as $file) {
            // Allow to access scope of the Container
            include_once $dirPrefixer($file);
        }
    }
}
