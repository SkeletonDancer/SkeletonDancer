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

use Rollerworks\Tools\SkeletonDancer\Profile;

final class ConfigFactory
{
    private $currentDir;
    private $projectDirectory;
    private $overwriteSetting;
    private $dancerDirectory;
    private $configFile;

    public function __construct(string $currentDir, string $projectDirectory)
    {
        $this->currentDir = ConfigFileLoader::normalizePath($currentDir);
        $this->projectDirectory = ConfigFileLoader::normalizePath($projectDirectory);
    }

    public function setConfigFile(string $file = null): ConfigFactory
    {
        $this->configFile = $file;

        return $this;
    }

    public function setDancerDirectory(string $directory = null): ConfigFactory
    {
        $this->dancerDirectory = ConfigFileLoader::normalizePath($directory);

        return $this;
    }

    public function setFileOverwrite(string $option = null): ConfigFactory
    {
        $this->overwriteSetting = $option;

        return $this;
    }

    public function create(): Config
    {
        $files = [__DIR__.'/../../Resources/config/base.yml'];

        if (null !== $this->configFile) {
            $files[] = $this->configFile;
        }

        $config = (new ConfigFileLoader($this->dancerDirectory))->processFiles($files);
        $config['config_file'] = $this->configFile;
        $config['dancer_directory'] = $this->dancerDirectory;
        $config['project_directory'] = $this->projectDirectory;
        $config['current_dir_name'] = basename($this->currentDir);
        $config['current_dir'] = $this->currentDir;

        if ($this->currentDir !== $this->projectDirectory) {
            $config['current_dir_relative'] = mb_substr($this->currentDir, strlen($this->projectDirectory) + 1);
        }

        if (null !== $this->overwriteSetting) {
            $config['overwrite'] = $this->overwriteSetting;
        }

        $profiles = $this->processProfiles($config['profiles']);
        unset($config['profiles']);

        return new Config($config, $profiles);
    }

    private function processProfiles(array $profiles): array
    {
        $processProfiles = [];

        foreach ($profiles as $name => $config) {
            $processProfiles[$name] = new Profile(
                $name,
                $config['generators'],
                $config['configurators'],
                $config['import'],
                $config['variables'],
                $config['defaults']
            );
        }

        return $processProfiles;
    }
}
