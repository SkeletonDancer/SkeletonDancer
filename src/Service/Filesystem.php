<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Service;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;

/**
 * Filesystem service for generators.
 *
 * This service is a wrapper around the Symfony Filesystem component.
 *
 * * All relative path's are resolved with the "current-directory".
 * * Depending on the configuration, overwriting existing files will eg.
 *   ask, abort, backup original, skip or continue the operation.
 * * Removing of files is not possible with this service, as generators should
 *   only generate content, not remove existing files.
 */
class Filesystem
{
    private $filesystem;
    private $style;

    /**
     * @var string
     */
    private $currentDir;

    /**
     * @var string
     */
    private $overwrite;

    /**
     * @var array
     */
    private $paths;

    public function __construct(SfFilesystem $filesystem, SymfonyStyle $style, array $paths, $overwrite = 'ask')
    {
        if (!isset($paths['currentDir'], $paths['projectDir'])) {
            throw new \InvalidArgumentException('Missing "currentDir" and/or "projectDir" in provided paths.');
        }

        $this->filesystem = $filesystem;
        $this->style = $style;
        $this->currentDir = $paths['currentDir'];
        $this->paths = $paths;
        $this->overwrite = $overwrite;
    }

    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     * @param string $originFile          The original filename
     * @param string $targetFile          The target filename
     * @param bool   $overwriteNewerFiles If true, target files newer than origin files are overwritten
     */
    public function copy($originFile, $targetFile, $overwriteNewerFiles = false)
    {
        $originFileFull = $this->resolvePath($originFile);
        $targetFileFull = $this->resolvePath($targetFile);

        if ('force' !== $this->overwrite && file_exists($targetFileFull)) {
            $doCopy = true;

            if (!$overwriteNewerFiles && null === parse_url($originFileFull, PHP_URL_HOST)) {
                $doCopy = filemtime($originFileFull) > filemtime($targetFileFull);
            }

            if (!$doCopy) {
                return;
            }

            if (!$this->fileExistsOperation($targetFileFull, $targetFile)) {
                return;
            }
        }

        $this->filesystem->copy($originFileFull, $targetFileFull, $overwriteNewerFiles);
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
     * @param array        $options   An array of boolean options
     *                                Valid options are:
     *                                - $options['override'] Whether to override an existing file on copy or not (see copy())
     */
    public function mirror($originDir, $targetDir, \Traversable $iterator = null, $options = [])
    {
        $targetDirFull = realpath($this->resolvePath($targetDir));
        $originDirFull = realpath($this->resolvePath($originDir));
        $override = isset($options['override']) ? $options['override'] : false;

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
                $this->copy($file, $target, $override);
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

        if (file_exists($file) &&
            (file_get_contents($file) === $content || !$this->fileExistsOperation($file, $filename))
        ) {
            return;
        }

        if ($this->style->isVeryVerbose()) {
            $this->style->comment('Dumping to file: '.$file);
        }

        $this->filesystem->dumpFile($file, $content);
    }

    /**
     * @internal
     *
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

        if ('@' !== $name[0]) {
            return $this->currentDir.'/'.$name;
        }

        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('Path "%s" contains invalid characters (..).', $name));
        }

        $dirPointer = substr($name, 1, strpos($name, '/') - 1);

        if (!isset($this->paths[$dirPointer])) {
            throw new \InvalidArgumentException(
                sprintf('Unable to resolve unknown directory-pointer "%s" for "%s".', $dirPointer, $name)
            );
        }

        $resolvedPath = substr_replace($name, $this->paths[$dirPointer], 0, strlen($dirPointer) + 1);

        return $resolvedPath;
    }

    private function fileExistsOperation($targetFile, $filename)
    {
        $overwrite = $this->overwrite;

        if ('ask' === $this->overwrite) {
            $options = ['a' => 'abort', 's' => 'skip', 'y' => 'overwrite', 'b' => 'backup'];
            $overwrite = $options[$this->style->choice(
                sprintf('File "%s" already exists, what to do?', $filename),
                $options,
                'abort'
            )];
        }

        switch ($overwrite) {
            case 'abort':
                throw new \RuntimeException(sprintf('File "%s" already exists. Aborted.', $filename));

            case 'skip':
                if ($this->style->isVerbose()) {
                    $this->style->note(sprintf('File "%s" already exists. Ignoring.', $filename));
                }

                return false;

            case 'backup':
                $this->createFileBackup($targetFile, $targetFile);
                break;
        }

        return true;
    }

    private function createFileBackup($origFile, $filename)
    {
        $backupFile = $backupFilePattern = $origFile.'.bak';
        $i = 0;

        while (file_exists($backupFile)) {
            if ($i > 10) {
                throw new RuntimeException(
                    sprintf('More then 10 back-up files exist for "%s", aborting.', $origFile)
                );
            }

            $backupFile = $backupFilePattern.$i;

            ++$i;
        }

        $this->filesystem->copy($origFile, $backupFile, true);
        $this->style->note(
            sprintf(
                'Original file "%s" backed-up as "%s".',
                $this->removePrefix($filename),
                $this->removePrefix($backupFile)
            )
        );
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

    /**
     * @internal
     *
     * @param string $name
     *
     * @return string
     */
    private function removePrefix($name)
    {
        if (!$this->filesystem->isAbsolutePath($name)) {
            return $name;
        }

        return mb_substr($name, mb_strlen($this->paths['projectDir']) + 1);
    }
}
