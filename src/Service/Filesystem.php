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

namespace SkeletonDancer\Service;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;

/**
 * Filesystem service for generators.
 *
 * This service is a wrapper around the Symfony Filesystem component.
 * Removing files is not possible with this service, as generators should
 * only generate content, not remove existing files.
 */
class Filesystem
{
    private $filesystem;
    private $currentDir;
    private $overwrite;

    public function __construct(SfFilesystem $filesystem, string $currentDir, bool $overwrite = false)
    {
        $this->filesystem = $filesystem;
        $this->currentDir = $currentDir;
        $this->overwrite = $overwrite;
    }

    /**
     * @return string
     */
    public function getCurrentDir(): string
    {
        return $this->currentDir;
    }

    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     * @param string $originFile The original filename
     * @param string $targetFile The target filename
     */
    public function copy($originFile, $targetFile)
    {
        $originFileFull = $this->resolvePath($originFile);
        $targetFileFull = $this->resolvePath($targetFile);

        if (file_exists($targetFileFull)) {
            if (hash_file('sha1', $originFileFull) === hash_file('sha1', $targetFileFull)) {
                return;
            }

            if (!$this->overwrite) {
                throw new IOException(sprintf('File "%s" already exists. Aborted.', $targetFileFull));
            }
        }

        $this->filesystem->copy($originFileFull, $targetFileFull);
    }

    /**
     * Creates a directory recursively.
     *
     * @param string|array|\Traversable $dirs The directory path
     * @param int                       $mode The directory mode
     */
    public function mkdir($dirs, $mode = 0777)
    {
        $this->filesystem->mkdir($this->toIterator($dirs), $mode);
    }

    /**
     * Checks the existence of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to check
     *
     * @return bool true if the file exists, false otherwise
     */
    public function exists($files)
    {
        return $this->filesystem->exists($this->toIterator($files));
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to create
     * @param int                       $time  The touch time as a Unix timestamp
     * @param int                       $atime The access time as a Unix timestamp
     */
    public function touch($files, $time = null, $atime = null)
    {
        $this->filesystem->touch($this->toIterator($files), $time, $atime);
    }

    /**
     * Change mode for an array of files or directories.
     *
     * @param string|array|\Traversable $files     A filename, an array of files, or a \Traversable instance to change mode
     * @param int                       $mode      The new mode (octal)
     * @param int                       $umask     The mode mask (octal)
     * @param bool                      $recursive Whether change the mod recursively or not
     */
    public function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        $this->filesystem->chmod($this->toIterator($files), $mode, $umask, $recursive);
    }

    /**
     * Change the owner of an array of files or directories.
     *
     * @param string|array|\Traversable $files     A filename, an array of files, or a \Traversable instance to change owner
     * @param string                    $user      The new owner user name
     * @param bool                      $recursive Whether change the owner recursively or not
     */
    public function chown($files, $user, $recursive = false)
    {
        $this->filesystem->chown($this->toIterator($files), $user, $recursive);
    }

    /**
     * Change the group of an array of files or directories.
     *
     * @param string|array|\Traversable $files     A filename, an array of files, or a \Traversable instance to change group
     * @param string                    $group     The group name
     * @param bool                      $recursive Whether change the group recursively or not
     */
    public function chgrp($files, $group, $recursive = false)
    {
        $this->filesystem->chgrp($this->toIterator($files), $group, $recursive);
    }

    /**
     * Given an existing path, convert it to a path relative to a given starting path.
     *
     * @param string $endPath Absolute path of target
     *
     * @return string Path of target relative to starting path
     */
    public function makePathRelative($endPath)
    {
        return $this->filesystem->makePathRelative($endPath, $this->currentDir);
    }

    /**
     * Mirrors a directory to another.
     *
     * @param string       $originDir The origin directory
     * @param string       $targetDir The target directory
     * @param \Traversable $iterator  A Traversable instance
     */
    public function mirror($originDir, $targetDir, \Traversable $iterator = null)
    {
        $targetDirFull = realpath($this->resolvePath($targetDir));
        $originDirFull = realpath($this->resolvePath($originDir));

        if (null === $iterator) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $originDirFull,
                    \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
                ), \RecursiveIteratorIterator::SELF_FIRST
            );
        }

        if ($this->filesystem->exists($originDirFull)) {
            $this->filesystem->mkdir($targetDirFull);
        }

        foreach ($iterator as $file) {
            $target = str_replace($originDirFull, $targetDirFull, $file->getPathname());

            if (is_file($file)) {
                $this->copy($file, $target);
            } elseif (is_dir($file)) {
                $this->filesystem->mkdir($target);
            } else {
                throw new IOException(sprintf('Unable to guess "%s" file type.', $file), 0, null, $file);
            }
        }
    }

    /**
     * Atomically dumps content into a file.
     *
     * When the file already exists the file may be backed-up, overwritten or the operation
     * is aborted (depending on the configuration).
     *
     * @param string $filename The file to be written to
     * @param string $content  The data to write into the file
     */
    public function dumpFile($filename, $content)
    {
        $file = $this->resolvePath($filename);

        if (file_exists($file) && file_get_contents($file) === $content) {
            return;
        }

        if (file_exists($file) && !$this->overwrite) {
            throw new IOException(sprintf('File "%s" already exists. Aborted.', $file));
        }

        $this->filesystem->dumpFile($file, $content);
    }

    /**
     * Reads the contents of a file.
     *
     * @param string $filename
     * @param bool   $allowMissing
     *
     * @return string|null
     */
    public function readFile(string $filename, bool $allowMissing = false): ?string
    {
        $file = $this->resolvePath($filename);

        if (!file_exists($file)) {
            if (!$allowMissing) {
                throw new IOException(sprintf('File "%s" does not exist.', $file));
            }

            return null;
        }

        return file_get_contents($file);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function resolvePath($name)
    {
        if ('' === $name) {
            throw new \InvalidArgumentException('An empty path is not valid to be resolved.');
        }

        if ($this->filesystem->isAbsolutePath($name)) {
            return $name;
        }

        if (false !== mb_strpos($name, '..')) {
            throw new \RuntimeException(sprintf('Path "%s" contains invalid characters (..).', $name));
        }

        return $this->currentDir.'/'.$name;
    }

    /**
     * @param mixed $files
     *
     * @return \Traversable
     */
    private function toIterator($files)
    {
        if (!$files instanceof \Traversable) {
            $files = new \ArrayObject(array_map([$this, 'resolvePath'], (array) $files));
        }

        return $files;
    }
}
