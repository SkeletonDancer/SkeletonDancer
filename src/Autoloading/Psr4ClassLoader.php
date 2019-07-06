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

use Symfony\Component\Debug\DebugClassLoader;

/**
 * A PSR-4 compatible class loader.
 *
 * See http://www.php-fig.org/psr/psr-4/
 *
 * @author Alexander M. Turek <me@derrabus.de>
 */
class Psr4ClassLoader
{
    private $prefixes = [];
    private $loaderInstance;

    public function addPrefix(string $prefix, string $baseDir)
    {
        $prefix = trim($prefix, '\\').'\\';
        $baseDir = rtrim($baseDir, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR;
        $this->prefixes[] = [$prefix, $baseDir];
    }

    public function findFile(string $class): ?string
    {
        $class = ltrim($class, '\\');

        foreach ($this->prefixes as list($currentPrefix, $currentBaseDir)) {
            if (0 === mb_strpos($class, $currentPrefix)) {
                $classWithoutPrefix = mb_substr($class, mb_strlen($currentPrefix));
                $file = $currentBaseDir.str_replace('\\', \DIRECTORY_SEPARATOR, $classWithoutPrefix).'.php';
                if (file_exists($file)) {
                    return $file;
                }
            }
        }

        return null;
    }

    public function loadClass(string $class): bool
    {
        $file = $this->findFile($class);
        if (null !== $file) {
            require $file;

            return true;
        }

        return false;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @codeCoverageIgnore
     */
    public function register()
    {
        spl_autoload_register($this->loaderInstance = [new DebugClassLoader([$this, 'loadClass']), 'loadClass'], true);
    }

    /**
     * Removes this instance from the registered autoloaders.
     *
     * @codeCoverageIgnore
     */
    public function unregister()
    {
        spl_autoload_unregister($this->loaderInstance);
    }
}
