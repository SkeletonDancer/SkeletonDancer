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

use Rollerworks\Tools\SkeletonDancer\Container;
use Webmozart\Console\Api\Event\PreHandleEvent;

final class ProjectDirectorySetupListener
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(PreHandleEvent $event)
    {
        if (isset($this->container['project_directory'])) {
            return;
        }

        $currentDirectory = str_replace('\\', '/', $this->container['current_dir']);
        $projectDirectory = $event->getArgs()->getOption('project-directory');

        if (!$projectDirectory) {
            $this->container['project_directory'] = $projectDirectory = $this->getProjectDirectory($currentDirectory);
        } else {
            $this->container['project_directory'] = $projectDirectoryResolved = str_replace('\\', '/', realpath($projectDirectory));

            if (!$projectDirectoryResolved) {
                throw new \InvalidArgumentException(sprintf('Project directory "%s" does not exist.', $projectDirectory));
            }

            if ($currentDirectory !== $projectDirectory && $projectDirectoryResolved !== mb_substr($currentDirectory, 0, mb_strlen($projectDirectoryResolved))) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The current directory "%s" does not belong to the project-directory "%s"',
                        $currentDirectory,
                        $projectDirectoryResolved
                    )
                );
            }
        }

        if (is_dir($projectDirectory.'/.dancer')) {
            $this->container['dancer_directory'] = $this->container['project_directory'].'/.dancer';
        }

        $this->container['config_file'] = $this->findDancerConfigFile(
            $projectDirectory.'/',
            $currentDirectory,
            $event->getArgs()->getOption('config-file')
        );
    }

    private function getProjectDirectory($workDir)
    {
        $configDir = $workDir.'/';

        while (is_dir($configDir)) {
            if (is_dir($configDir.'.dancer')) {
                return realpath($configDir);
            }

            $configDir .= '../';
        }

        return $workDir;
    }

    /**
     * @param string $projectDirectory
     * @param string $currentDirectory
     * @param string $value
     *
     * @return null|string
     */
    private function findDancerConfigFile($projectDirectory, $currentDirectory, $value = null)
    {
        if (null === $value) {
            return;
        }

        if ('' === $value) {
            $searchLocations = [$currentDirectory.'.dancer.yml', $projectDirectory.'.dancer.yml'];
            $allowFailure = true;
        } else {
            $searchLocations = [$value];
            $allowFailure = false;
        }

        foreach ($searchLocations as $file) {
            if (file_exists($file)) {
                return realpath($file);
            }
        }

        if (!$allowFailure) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unable to load configuration file, no such file. Searched in the following locations: "%s".',
                    implode('", "', $searchLocations)
                )
            );
        }
    }
}
