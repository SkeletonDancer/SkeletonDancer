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

class Dances
{
    protected $dancesDirectory;
    protected $dances = [];
    protected $loader;

    public function __construct(string $dancesDirectory, IO $io, Loader $loader)
    {
        if (!is_dir($dancesDirectory)) {
            throw new IOException(sprintf('Directory "%s" does not exist.', $dancesDirectory));
        }

        $this->loader = $loader;
        $this->loadDances($dancesDirectory, $io);
    }

    public function has(string $name): bool
    {
        return isset($this->dances[$name]);
    }

    public function get(string $name): Dance
    {
        if (isset($this->dances[$name])) {
            return $this->dances[$name];
        }

        throw new \InvalidArgumentException(sprintf('Dance "%s" is not installed.', $name));
    }

    public function all(): array
    {
        return $this->dances;
    }

    public function getDancesDirectory(): string
    {
        return $this->dancesDirectory;
    }

    protected function buildDance(string $danceDirectory, IO $io, string $name): ?Dance
    {
        try {
            return $this->loader->load($danceDirectory, $name);
        } catch (\Exception $e) {
            $io->errorLine('Dance "'.$name.'" is damaged: '.ltrim(StringUtil::indentLines($e->getMessage())));

            if ($io->isVeryVerbose()) {
                $io->errorLine(StringUtil::indentLines($e->getTraceAsString(), 1, '  '));
            }

            return null;
        }
    }

    private function loadDances(string $dancesDirectory, IO $io): void
    {
        foreach (new \DirectoryIterator($dancesDirectory) as $node) {
            if (!$node->isDir() || $node->isDot()) {
                continue;
            }

            $vendorName = $node->getFilename();

            foreach (new \DirectoryIterator($dancesDirectory.'/'.$vendorName) as $danceRepo) {
                if (!$danceRepo->isDir() || $danceRepo->isDot()) {
                    continue;
                }

                $name = $vendorName.'/'.$danceRepo->getFilename();

                if (null !== $dance = $this->buildConfiguration($dancesDirectory, $io, $name)) {
                    $this->dances[$name] = $dance;
                }
            }
        }
        $this->dancesDirectory = $dancesDirectory;
    }

    private function buildConfiguration(string $routineDirectory, IO $io, string $name): ?Dance
    {
        $danceDirectory = $routineDirectory.'/'.$name;

        if (!is_dir($routineDirectory.'/'.$name.'/.git')) {
            $io->errorLine(
                sprintf('Dance "%s" is damaged: Missing .git directory in "%s".', $name, $routineDirectory.'/'.$name)
            );

            return null;
        }

        return $this->buildDance($danceDirectory, $io, $name);
    }
}
