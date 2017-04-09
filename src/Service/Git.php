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

namespace Rollerworks\Tools\SkeletonDancer\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;

class Git
{
    /**
     * @var CliProcess
     */
    private $process;

    public function __construct(CliProcess $process)
    {
        $this->process = $process;
    }

    /**
     * Returns whether the current directory is a Git directory.
     *
     * @param bool $requireRoot Require directory is the root of the Git repository,
     *                          default is true
     *
     * @return bool Whether we are inside a git directory or not
     */
    public function isGitDirectory($requireRoot = true)
    {
        try {
            $process = $this->process->mustRun(['git', 'rev-parse', '--show-toplevel']);
            $topFolder = $process->getOutput();

            if ($requireRoot && str_replace('\\', '/', getcwd()) !== $topFolder) {
                return false;
            }
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }

    public function initRepo()
    {
        $this->process->mustRun(['git', 'init']);
    }

    public function addRemote($name, $url)
    {
        if (!$this->hasGitConfig('remote.'.$name.'.url')) {
            $this->process->mustRun(['git', 'remote', 'add', $name, $url]);
        } else {
            $this->setGitConfig('remote.'.$name.'.url', $url, true);
        }
    }

    public function updateGitIgnore($patterns, $append = true)
    {
    }

    public function updateGitAttributes($patterns, $append = true)
    {
    }

    /**
     * @param string $config
     * @param string $section
     * @param null   $expectedValue
     *
     * @return bool
     */
    public function hasGitConfig($config, $section = 'local', $expectedValue = null)
    {
        $process = $this->process->run(['git', 'config', '--'.$section, '--get', $config]);
        $value = trim($process->getOutput());

        if ('' === $value || (null !== $expectedValue && $value !== $expectedValue)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $config
     * @param string $value
     * @param bool   $overwrite
     * @param string $section
     */
    public function setGitConfig($config, $value, $overwrite = false, $section = 'local')
    {
        if ($this->hasGitConfig($config, $section, $value) && !$overwrite) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to set git config "%s" at %s, because the value is already set.',
                    $config,
                    $section
                )
            );
        }

        $this->process->mustRun(['git', 'config', $config, $value, '--'.$section]);
    }

    /**
     * @param string $config
     * @param string $section
     * @param bool   $all
     *
     * @return string
     */
    public function getGitConfig($config, $section = 'local', $all = false)
    {
        $process = $this->process->run(
            ['git', 'config', '--'.$section, '--'.($all ? 'get-all' : 'get'), $config]
        );

        return trim($process->getOutput());
    }
}
