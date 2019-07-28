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

namespace SkeletonDancer\Configuration;

use Psr\Log\LoggerInterface;
use SkeletonDancer\Dance;
use SkeletonDancer\Dances;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @internal
 * @final
 */
class DancesProvider
{
    private $workDir;
    private $dancesDirectory;

    private $logger;
    private $loader;

    public function __construct(string $workDir, string $dancesDirectory, Loader $loader, LoggerInterface $logger)
    {
        if (!is_dir($dancesDirectory)) {
            throw new IOException(sprintf('Directory "%s" does not exist.', $dancesDirectory));
        }

        $this->workDir = $workDir;
        $this->dancesDirectory = $dancesDirectory;

        $this->loader = $loader;
        $this->logger = $logger;
    }

    public function all(): Dances
    {
        return new Dances(array_merge($this->global()->all(), $this->local()->all()));
    }

    public function global(): Dances
    {
        $dances = [];

        foreach (new \DirectoryIterator($this->dancesDirectory) as $node) {
            if (!$node->isDir() || $node->isDot()) {
                continue;
            }

            $vendorName = $node->getFilename();
            $dances += $this->loadFromDirectory($this->dancesDirectory.'/'.$vendorName, $vendorName);
        }

        return new Dances($dances);
    }

    public function local(): Dances
    {
        $dancesDirectory = $this->getLocalDancesFolder();

        if (null === $dancesDirectory) {
            return new Dances();
        }

        return new Dances($this->loadFromDirectory($dancesDirectory, '_local'));
    }

    private function getLocalDancesFolder(): ?string
    {
        $dancesDirectory = $this->workDir.'/';

        while (is_dir($dancesDirectory)) {
            if (is_dir($dancesDirectory.'.dances')) {
                $dancesDirectory = realpath($dancesDirectory.'.dances');

                break;
            }

            $dancesDirectory .= '../';
        }

        if ('.dances' !== mb_substr($dancesDirectory, -7)) {
            $this->logger->info('No local ".dances" directory could be found.');

            return null;
        }

        $this->logger->info('Found local ".dances" directory at "{directory}".', ['directory' => $dancesDirectory]);

        return $dancesDirectory;
    }

    /**
     * @return Dance[]
     */
    private function loadFromDirectory(string $directory, string $vendorName): array
    {
        if (!is_dir($directory)) {
            throw new IOException(sprintf('Directory "%s" does not exist.', $directory));
        }

        $dances = [];

        foreach (new \DirectoryIterator($directory) as $node) {
            if ($node->isDot() || !$node->isDir()) {
                continue;
            }

            $dirname = $node->getFilename();
            $name = $vendorName.'/'.$dirname;

            if (null !== ($dance = $this->buildDance($directory.'/'.$dirname, $name))) {
                $dances[$name] = $dance;
            }
        }

        return $dances;
    }

    private function buildDance(string $danceDirectory, string $name): ?Dance
    {
        try {
            return $this->loader->load($danceDirectory, $name);
        } catch (\Exception $e) {
            $this->logger->error('Dance "{dance}" is damaged: {message}', ['dance' => $name, 'message' => ltrim($e->getMessage())]);

            return null;
        }
    }
}
