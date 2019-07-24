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

use SkeletonDancer\Configuration\Loader;
use Symfony\Component\Filesystem\Exception\IOException;
use Webmozart\Console\Api\IO\IO;

class LocalDances extends Dances
{
    public function __construct(string $workDir, IO $io, Loader $loader)
    {
        $this->loader = $loader;

        $dancesDirectory = $workDir.'/';

        while (is_dir($dancesDirectory)) {
            if (is_dir($dancesDirectory.'.dances')) {
                $dancesDirectory = realpath($dancesDirectory.'.dances');

                break;
            }
            $dancesDirectory .= '../';
        }

        if ('.dances' !== mb_substr($dancesDirectory, -7)) {
            throw new IOException('No local ".dances" directory could be found.');
        }

        $this->loadLocalDances($dancesDirectory, $io);
    }

    private function loadLocalDances(string $dancesDirectory, IO $io): void
    {
        foreach (new \DirectoryIterator($dancesDirectory) as $danceDir) {
            if ($danceDir->isDot() || !$danceDir->isDir() || '.dance' !== mb_substr($name = $danceDir->getFilename(), -6)) {
                continue;
            }

            $title = mb_substr($name, 0, -6);

            if (null !== $dance = $this->buildDance($dancesDirectory.'/'.$name, $io, $title)) {
                $this->dances[$title] = $dance;
            }
        }

        $this->dancesDirectory = $dancesDirectory;
    }
}
