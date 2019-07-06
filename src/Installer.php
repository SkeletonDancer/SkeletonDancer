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
use SkeletonDancer\Service\CliProcess;
use SkeletonDancer\Service\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem as SfFilesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

class Installer
{
    private $hosting;
    private $process;
    private $dances;
    private $loader;
    private $dancesDirectory;
    private $executableFinder;
    private $filesystem;

    public function __construct(
        Hosting $hosting,
        CliProcess $process,
        Dances $dances,
        Loader $loader = null,
        ExecutableFinder $executableFinder = null,
        Filesystem $filesystem = null
    ) {
        $this->hosting = $hosting;
        $this->process = $process;
        $this->dances = $dances;
        $this->dancesDirectory = $this->dances->getDancesDirectory();
        $this->executableFinder = $executableFinder ?? new ExecutableFinder();
        $this->loader = $loader ?? new Loader();
        $this->filesystem = $filesystem ?? new Filesystem(new SfFilesystem(), '/tmp', true);
    }

    public function install(string $name, ?string $version = null): ?Dance
    {
        $gitPath = $this->executableFinder->find('git');
        $message = '';

        if (null === $gitPath) {
            throw new \InvalidArgumentException(sprintf(
                'You do not seem to have Git installed on your system?'
            ));
        }

        if (!$this->dances->has($name) && !$this->hosting->supports($name, $version, $message)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unable to find dance "%s" with version "%s" in the hosting, message: %s',
                    $name,
                    (string) $version,
                    $message
                )
            );
        }

        if ($this->dances->has($name)) {
            $this->updateRepo($name, $gitPath, $version);
        } else {
            $this->cloneRepo($name, $gitPath, $version);
        }

        return $this->loader->load($this->dancesDirectory.'/'.$name, $name);
    }

    private function cloneRepo(string $name, string $gitPath, ?string $version = null): void
    {
        $versionFile = $this->dancesDirectory.'/'.$name.'/.git/.dancer_version';
        $process = $this->process->run(
            [$gitPath, 'clone', $this->hosting->getRepositoryUrl($name), $this->dancesDirectory.'/'.$name],
            null,
            null,
            OutputInterface::VERBOSITY_NORMAL
        );

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (null === $version) {
            $process = $this->process->mustRun(
                (new ProcessBuilder([$gitPath, 'rev-parse', '--abbrev-ref', 'HEAD']))
                    ->setWorkingDirectory(
                    $this->dancesDirectory.'/'.$name
                )->getProcess()
            );

            $activeBranch = trim($process->getOutput());
            $this->filesystem->dumpFile($versionFile, 'origin/'.$activeBranch);
        } else {
            $this->setVersion($name, $gitPath, $versionFile, $version);
        }
    }

    private function updateRepo(string $name, string $gitPath, ?string $version = null): void
    {
        $versionFile = $this->dancesDirectory.'/'.$name.'/.git/.dancer_version';
        if ($versionInfo = explode('/', (string) $this->filesystem->readFile($versionFile, true), 2)) {
            $isBranch = 'origin' === $versionInfo[0];
            $curVersion = $versionInfo[1];
        } else {
            $isBranch = true;
            $curVersion = 'origin/master';
        }

        // Existing tags not cannot be updated.
        if (!$isBranch && (null === $version || $curVersion === $version)) {
            return;
        }

        $process = new ProcessBuilder([$gitPath, 'fetch', '--tags', '--all']);
        $process->setWorkingDirectory($this->dancesDirectory.'/'.$name);
        $this->process->mustRun($process->getProcess());

        $this->setVersion($name, $gitPath, $versionFile, $version ?? $curVersion);
    }

    private function setVersion(string $name, string $gitPath, $versionFile, string $version): void
    {
        if (\in_array($version, $this->getTags($name, $gitPath), true)) {
            $ref = 'tags/'.$version;
        } elseif (\in_array($version, $this->getBranches($name, $gitPath), true)) {
            $ref = 'origin/'.$version;
        } else {
            throw new \RuntimeException('No tag or branch exist for this version.');
        }

        $this->process->mustRun(
            (new ProcessBuilder([$gitPath, 'checkout', $ref, '-f']))->setWorkingDirectory(
                $this->dancesDirectory.'/'.$name
            )->getProcess()
        );

        $this->filesystem->dumpFile($versionFile, $ref);
    }

    private function getBranches(string $name, $gitPath): array
    {
        // Cannot use '%(refname:strip=3)' because Travis is still on Git 1.6.x
        $process = new ProcessBuilder([$gitPath, 'for-each-ref', '--format', '%(refname)', 'refs/remotes/origin']);
        $process->setWorkingDirectory($this->dancesDirectory.'/'.$name);

        $branches = StringUtil::splitLines($this->process->mustRun($process->getProcess())->getOutput());
        $branches = array_map(function (string $ref): string {
            return mb_substr($ref, 20);
        }, $branches);

        return $branches;
    }

    private function getTags(string $name, $gitPath): array
    {
        $process = new ProcessBuilder([$gitPath, 'tag', '--list']);
        $process->setWorkingDirectory($this->dancesDirectory.'/'.$name);

        return StringUtil::splitLines($this->process->mustRun($process->getProcess())->getOutput());
    }
}
