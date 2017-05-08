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

use Symfony\Component\Process\Exception\ProcessFailedException;

class Git
{
    private $process;

    public function __construct(CliProcess $process)
    {
        $this->process = $process;
    }

    /**
     * Returns whether the current directory is a Git repository.
     *
     * @param bool $requireRoot Require directory is the root of the Git repository,
     *                          default is true
     *
     * @return bool
     */
    public function isGitDirectory(bool $requireRoot = true): bool
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

    public function initRepo(): void
    {
        $this->process->mustRun(['git', 'init']);
    }

    public function hasGitConfig(string $config, string $section = 'local', string $expectedValue = null): bool
    {
        $process = $this->process->run(['git', 'config', '--'.$section, '--get', $config]);
        $value = trim($process->getOutput());

        if ('' === $value || (null !== $expectedValue && $value !== $expectedValue)) {
            return false;
        }

        return true;
    }

    public function setGitConfig(string $config, string $value, bool $overwrite = false)
    {
        if ($this->hasGitConfig($config, 'local', $value) && !$overwrite) {
            throw new \RuntimeException(
                sprintf('Unable to set git config "%s", because the value is already set.', $config)
            );
        }

        $this->process->mustRun(['git', 'config', $config, $value, '--local']);
    }

    public function getGitConfig(string $config, string $section = 'local', bool $all = false): string
    {
        $process = $this->process->run(
            ['git', 'config', '--'.$section, '--'.($all ? 'get-all' : 'get'), $config]
        );

        return trim($process->getOutput());
    }
}
