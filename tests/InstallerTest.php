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

namespace SkeletonDancer\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SkeletonDancer\Configuration\Loader;
use SkeletonDancer\Dance;
use SkeletonDancer\Dances;
use SkeletonDancer\Hosting;
use SkeletonDancer\Installer;
use SkeletonDancer\Service\CliProcess;
use SkeletonDancer\Service\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class InstallerTest extends TestCase
{
    /** @test */
    public function it_install_when_supported()
    {
        $dance = new Dance('dummy/dummy', '/var/tmp/.dances/dummy/dummy');

        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessInstall(),
            $this->createDances(),
            $this->createLoader($dance),
            $this->createExecutableFinder(),
            $this->createFilesystem('/var/tmp/.dances/dummy/dummy/.git/.dancer_version', null, 'origin/master')
        );

        self::assertSame($dance, $installer->install('dummy/dummy'));
    }

    /** @test */
    public function it_updates_when_already_installed()
    {
        $dance = new Dance('dummy/dummy2', '/var/tmp/.dances/dummy/dummy2');

        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(true, 'origin/master'),
            $this->createDances(),
            $this->createLoader($dance),
            $this->createExecutableFinder(),
            $this->createFilesystem('/var/tmp/.dances/dummy/dummy2/.git/.dancer_version', 'origin/master', 'origin/master')
        );

        self::assertSame($dance, $installer->install('dummy/dummy2'));
    }

    /** @test */
    public function it_updates_when_already_installed_and_sets_version()
    {
        $dance = new Dance('dummy/dummy2', '/var/tmp/.dances/dummy/dummy2');

        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(),
            $this->createDances(),
            $this->createLoader($dance),
            $this->createExecutableFinder(),
            $this->createFilesystem('/var/tmp/.dances/dummy/dummy2/.git/.dancer_version', 'origin/master', 'origin/latest')
        );

        self::assertSame($dance, $installer->install('dummy/dummy2', 'latest'));
    }

    /** @test */
    public function it_updates_when_already_installed_and_sets_version_of_tag()
    {
        $dance = new Dance('dummy/dummy2', '/var/tmp/.dances/dummy/dummy2');

        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(true, 'tags/v0.1.0'),
            $this->createDances(),
            $this->createLoader($dance),
            $this->createExecutableFinder(),
            $this->createFilesystem('/var/tmp/.dances/dummy/dummy2/.git/.dancer_version', 'origin/master', 'tags/v0.1.0')
        );

        self::assertSame($dance, $installer->install('dummy/dummy2', 'v0.1.0'));
    }

    /** @test */
    public function it_does_nothing_when_dance_is_already_installed_and_current_version_is_a_tag()
    {
        $dance = new Dance('dummy/dummy2', '/var/tmp/.dances/dummy/dummy2');

        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(false),
            $this->createDances(),
            $this->createLoader($dance),
            $this->createExecutableFinder(),
            $this->createFilesystem('/var/tmp/.dances/dummy/dummy2/.git/.dancer_version', 'tags/v0.1.0')
        );

        self::assertSame($dance, $installer->install('dummy/dummy2'));
    }

    /** @test */
    public function it_checks_if_hosting_supports_dance()
    {
        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(),
            $this->createDances(),
            $this->createLoader(),
            $this->createExecutableFinder(),
            $this->createFilesystem()
        );

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unable to find dance "noop/dummy" with version "" in the hosting, message:');

        $installer->install('noop/dummy');
    }

    /** @test */
    public function it_checks_if_hosting_supports_dance_with_version()
    {
        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(),
            $this->createDances(),
            $this->createLoader(),
            $this->createExecutableFinder(),
            $this->createFilesystem()
        );

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unable to find dance "noop/dummy" with version "v1.0.0" in the hosting, message:');

        $installer->install('noop/dummy', 'v1.0.0');
    }

    /** @test */
    public function it_checks_git_is_installed()
    {
        $installer = new Installer(
            $this->createHosting(),
            $this->createProcessUpdate(),
            $this->createDances(),
            $this->createLoader(),
            $this->createExecutableFinder(null),
            $this->createFilesystem()
        );

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('You do not seem to have Git installed on your system?');

        $installer->install('noop/dummy');
    }

    protected function createHosting(): Hosting
    {
        $hostingProphecy = $this->prophesize(Hosting::class);
        $hostingProphecy->supports('dummy/dummy', null, Argument::any())->willReturn(true);
        $hostingProphecy->supports('dummy/dummy', 'stable', Argument::any())->willReturn(true);
        $hostingProphecy->getRepositoryUrl('dummy/dummy')->willReturn('https://github.com/dummy/dummy.dance.git');
        $hostingProphecy->getWebUrl('dummy/dummy')->willReturn('https://github.com/dummy/dummy.dance/');

        $hostingProphecy->supports('dummy/dummy2', null, Argument::any())->willReturn(true);
        $hostingProphecy->supports('dummy/dummy2', 'stable', Argument::any())->willReturn(true);
        $hostingProphecy->supports('dummy/dummy2', 'latest', Argument::any())->willReturn(true);
        $hostingProphecy->supports('dummy/dummy2', 'v0.1.0', Argument::any())->willReturn(true);
        $hostingProphecy->supports('dummy/dummy2', 'v1.0.0', Argument::any())->willReturn(false);
        $hostingProphecy->getRepositoryUrl('dummy/dummy2')->willReturn('https://github.com/dummy/dummy2.dance.git');
        $hostingProphecy->getWebUrl('dummy/dummy2')->willReturn('https://github.com/dummy/dummy2.dance/');

        $hostingProphecy->supports('noop/dummy', Argument::any(), Argument::any())->willReturn(false);
        $hostingProphecy->getRepositoryUrl('noop/dummy')->willThrow(new \InvalidArgumentException('noop/dummy is not supported'));
        $hostingProphecy->getWebUrl('noop/dummy')->willThrow(new \InvalidArgumentException('noop/dummy is not supported'));

        return $hostingProphecy->reveal();
    }

    private function createProcessInstall(string $name = 'dummy/dummy', bool $success = true): CliProcess
    {
        $process = $this->prophesize(Process::class);
        $process->isSuccessful()->willReturn($success);

        $processProphecy = $this->prophesize(CliProcess::class);
        $processProphecy->run(
            ['git.sh', 'clone', 'https://github.com/'.$name.'.dance.git', '/var/tmp/.dances/'.$name],
            null,
            null,
            OutputInterface::VERBOSITY_NORMAL
        )->willReturn($process->reveal());

        $processProphecy->run(
            ['git.sh', 'clone', 'https://github.com/'.$name.'.dance.git', '/var/tmp/.dances/'.$name],
            null,
            null,
            OutputInterface::VERBOSITY_NORMAL
        )->willReturn($process->reveal());

        $process = $this->prophesize(Process::class);
        $process->getOutput()->willReturn('master');

        $processProphecy->mustRun(
            Argument::that(function (Process $process): bool {
                self::assertEquals('git.sh rev-parse --abbrev-ref HEAD', str_replace(['"', "'"], '', $process->getCommandLine()));
                self::assertEquals('/var/tmp/.dances/dummy/dummy', $process->getWorkingDirectory());

                return true;
            })
        )->willReturn($process->reveal());

        return $processProphecy->reveal();
    }

    private function createProcessUpdate(bool $success = true, string $version = 'origin/latest'): CliProcess
    {
        $process = $this->prophesize(Process::class);
        $process->isSuccessful()->willReturn($success);

        $processProphecy = $this->prophesize(CliProcess::class);
        $processProphecy->mustRun(
            Argument::that(function (Process $process): bool {
                self::assertEquals('/var/tmp/.dances/dummy/dummy2', $process->getWorkingDirectory());

                return 'git.sh fetch --tags --all' === str_replace(['"', "'"], '', $process->getCommandLine());
            })
        )->willReturn($process->reveal());

        if (!$success) {
            return $processProphecy->reveal();
        }

        // Tags
        $process = $this->prophesize(Process::class);
        $process->isSuccessful()->willReturn($success);
        $process->getOutput()->willReturn("v0.1.0\nv0.2.0\n");

        $processProphecy->mustRun(
            Argument::that(function (Process $process): bool {
                self::assertEquals('/var/tmp/.dances/dummy/dummy2', $process->getWorkingDirectory());

                return 'git.sh tag --list' === str_replace(['"', "'"], '', $process->getCommandLine());
            })
        )->willReturn($process->reveal());

        // Branches
        $process = $this->prophesize(Process::class);
        $process->isSuccessful()->willReturn($success);
        $process->getOutput()->willReturn("refs/remotes/origin/master\nrefs/remotes/origin/latest\nrefs/remotes/origin/stable\n");

        $processProphecy->mustRun(
            Argument::that(function (Process $process): bool {
                self::assertEquals('/var/tmp/.dances/dummy/dummy2', $process->getWorkingDirectory());

                return 'git.sh for-each-ref --format %(refname) refs/remotes/origin' ===
                       str_replace(['"', "'"], '', $process->getCommandLine());
            })
        )->willReturn($process->reveal());

        // Checkout actual version
        $processProphecy->mustRun(
            Argument::that(function (Process $process) use ($version): bool {
                self::assertEquals('/var/tmp/.dances/dummy/dummy2', $process->getWorkingDirectory());

                return 'git.sh checkout '.$version.' -f' === str_replace(['"', "'"], '', $process->getCommandLine());
            })
        )->willReturn($process->reveal());

        return $processProphecy->reveal();
    }

    private function createDances(): Dances
    {
        $dancesProphecy = $this->prophesize(Dances::class);
        $dancesProphecy->getDancesDirectory()->willReturn('/var/tmp/.dances');

        $dancesProphecy->has('dummy/dummy')->willReturn(false);
        $dancesProphecy->get('dummy/dummy')->willReturn(new Dance('dummy/dummy', '/var/tmp/.dances/dummy/dummy'));

        $dancesProphecy->has('dummy/dummy2')->willReturn(true);
        $dancesProphecy->get('dummy/dummy2')->willReturn(new Dance('dummy/dummy2', '/var/tmp/.dances/dummy/dummy2'));

        $dancesProphecy->has('noop/dummy')->willReturn(false);

        return $dancesProphecy->reveal();
    }

    private function createLoader(Dance $dance = null): Loader
    {
        $loaderProphecy = $this->prophesize(Loader::class);
        $loaderProphecy->load('/var/tmp/.dances/dummy/dummy', 'dummy/dummy')->willReturn($dance);
        $loaderProphecy->load('/var/tmp/.dances/dummy/dummy2', 'dummy/dummy2')->willReturn($dance);

        return $loaderProphecy->reveal();
    }

    private function createExecutableFinder(?string $result = 'git.sh'): ExecutableFinder
    {
        $executableFinderProphecy = $this->prophesize(ExecutableFinder::class);
        $executableFinderProphecy->find('git')->willReturn($result);

        return $executableFinderProphecy->reveal();
    }

    private function createFilesystem(
        string $filename = null,
        string $expectRead = null,
        string $expectDump = null
    ): Filesystem {
        $filesystemProphecy = $this->prophesize(Filesystem::class);

        if ($expectRead) {
            $filesystemProphecy->readFile($filename, true)->willReturn($expectRead);
        }

        if ($expectDump) {
            $filesystemProphecy->dumpFile($filename, $expectDump)->shouldBeCalled();
        }

        return $filesystemProphecy->reveal();
    }
}
