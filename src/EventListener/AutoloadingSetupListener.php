<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\EventListener;

use Composer\Autoload\ClassLoader;
use Rollerworks\Tools\SkeletonDancer\Container;
use Webmozart\Console\Api\Event\PreHandleEvent;

final class AutoLoadingSetupListener
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(PreHandleEvent $event)
    {
        if (isset($this->container['autoloading_setup'])) {
            return;
        }

        $this->container['autoloading_setup'] = true;
        $config = $this->container['config'];

        /** @var ClassLoader $autoload */
        $autoload = require __DIR__.'/../../vendor/autoload.php';

        $projectDir = $config->get('project_directory').'/';

        // Prefix directory with the project-directory.
        // And ensures the correct directory separator (to prevent mismatches).
        $dirPrefixer = function ($dir) use ($projectDir) {
            $path = $projectDir.trim($dir, '/');

            if ('\\' === DIRECTORY_SEPARATOR) {
                $path = str_replace('\\', '//', $path);
            }

            return $path;
        };

        foreach ($config->get(['autoloading', 'psr-4'], []) as $prefix => $dirs) {
            $autoload->addPsr4($prefix, array_map($dirPrefixer, (array) $dirs));
        }

        foreach ($config->get(['autoloading', 'files'], []) as $file) {
            includeFile($projectDir.$file);
        }
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function includeFile($file)
{
    include_once $file;
}
