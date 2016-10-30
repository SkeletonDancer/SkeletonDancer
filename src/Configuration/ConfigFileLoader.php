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

namespace Rollerworks\Tools\SkeletonDancer\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * ConfigFileLoader loads configuration from a YAML file and it's children.
 */
final class ConfigFileLoader
{
    private $locator;
    private $configs = [];

    /**
     * Constructor.
     *
     * @param string|null $dancerDirectory The dancer directory to use
     *                                     for searching configuration files
     */
    public function __construct($dancerDirectory = null)
    {
        $configFolders = [];

        if ($dancerDirectory) {
            $configFolders[] = $dancerDirectory;
        }

        $this->locator = new FileLocator($configFolders);
    }

    /**
     * Processes an array of files to a normalized configuration.
     *
     * @param array $files
     *
     * @return array
     */
    public function processFiles(array $files)
    {
        foreach ($files as $file) {
            $this->loadFile($file);
        }

        return (new Processor())->processConfiguration(new Configuration(), $this->configs);
    }

    /**
     * Normalize the path (realpath and forward slashes).
     *
     * @param string $path
     *
     * @return string|null
     */
    public static function normalizePath(string $path = null)
    {
        if (null === $path) {
            return;
        }

        if (false === $realPath = realpath($path)) {
            throw new \InvalidArgumentException(
                sprintf('Unable to normalize path "%s", no such file or directory.', $path)
            );
        }

        return str_replace('\\', '//', $realPath);
    }

    private function loadFile($filename, $currentLocation = null, $loading = [])
    {
        $filename = $this->locator->locate($filename, $currentLocation, true);
        $filename = self::normalizePath($filename);

        if (is_dir($filename)) {
            throw new \InvalidArgumentException(
                sprintf('Path "%s" is not a valid file, recursive directory loading is not supported.', $filename)
            );
        }

        $currentLocation = dirname($filename);

        if (in_array($filename, $loading, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'File "%s" is already being loaded with the following order: "%s".',
                    $filename,
                    implode('", "', $loading)
                )
            );
        }

        $loading[] = $filename;

        try {
            $config = Yaml::parse(file_get_contents($filename));
        } catch (ParseException $e) {
            $e->setParsedFile($filename);

            throw $e;
        }

        if (null !== $config && !is_array($config)) {
            throw new \InvalidArgumentException(
                sprintf('Expected file "%s" to contain an array structure.', $filename)
            );
        }

        if (isset($config['import'])) {
            $imports = (array) $config['import'];

            foreach ($imports as $import) {
                $this->loadFile($import, $currentLocation, $loading);
            }
        }

        unset($config['import']);

        if ($config) {
            $this->configs[] = $config;
        }
    }
}
