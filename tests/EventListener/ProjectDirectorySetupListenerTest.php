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

namespace Rollerworks\Tools\SkeletonDancer\Tests\EventListener;

use Rollerworks\Tools\SkeletonDancer\Container;
use Rollerworks\Tools\SkeletonDancer\EventListener\ProjectDirectorySetupListener;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Event\PreHandleEvent;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\IO\InputStream\NullInputStream;
use Webmozart\Console\IO\OutputStream\NullOutputStream;

final class ProjectDirectorySetupListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ProjectDirectorySetupListener
     */
    private $listener;

    /**
     * @var string
     */
    private $tmpFs;

    /**
     * @var IO
     */
    private $io;

    /**
     * @var CommandConfig
     */
    private $commandConfig;

    /** @before */
    public function setUpListener()
    {
        if (!$this->tmpFs = sys_get_temp_dir()) {
            $this->markTestSkipped('No system temp folder configured.');
        }

        $this->tmpFs .= '/skeletondancer/tests/';

        $this->container = new Container();
        $this->listener = new ProjectDirectorySetupListener($this->container);

        $this->io = new IO(
            new Input(new NullInputStream()),
            new Output(new NullOutputStream()),
            new Output(new NullOutputStream())
        );
        $this->io->setInteractive(true);

        $this->commandConfig = CommandConfig::create()
            ->setName('generate')
            ->addOption('project-directory', null, Option::OPTIONAL_VALUE)
            ->addOption('config-file', null, Option::OPTIONAL_VALUE | Option::NULLABLE, null, '')
        ;
    }

    /** @test */
    public function it_detects_the_project_directory_as_current_when_no_dot_dancer_directory_is_found()
    {
        $this->container['current_dir'] = $this->mkDir('org/my-project1/sub');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->listener->__invoke($this->createEvent());

        $this->assertArrayHasKey('project_directory', $this->container);
        $this->assertEquals($this->container['current_dir'], $this->container['project_directory']);
        $this->assertNull($this->container['config_file']);
    }

    /** @test */
    public function it_can_only_be_configured_once()
    {
        $this->container['current_dir'] = $this->mkDir('org/my-project1/sub');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->listener->__invoke($this->createEvent());
        $this->listener->__invoke($this->createEvent('org/my-project1/sub'));

        $this->assertArrayHasKey('project_directory', $this->container);
        $this->assertEquals($this->container['current_dir'], $this->container['project_directory']);
    }

    /** @test */
    public function it_detects_the_project_directory_as_first_parent_with_a_dot_dancer_directory()
    {
        $this->container['current_dir'] = $this->mkDir('org/my-project2/sub');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $path = dirname($this->mkDir('org/my-project2/.dancer'));
        touch($path.'/.dancer.yml');

        $this->listener->__invoke($this->createEvent());

        $this->assertArrayHasKey('project_directory', $this->container);
        $this->assertEquals($path, $this->container['project_directory']);
        $this->assertEquals($path.DIRECTORY_SEPARATOR.'.dancer.yml', $this->container['config_file']);
    }

    /** @test */
    public function it_allows_the_project_directory_as_command_option()
    {
        $this->container['current_dir'] = $this->mkDir('org2/my-project/sub');
        $path = dirname(dirname($this->container['current_dir']));
        touch($path.'/.dancer.yml');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->listener->__invoke($this->createEvent($path));

        $this->assertArrayHasKey('project_directory', $this->container);
        $this->assertEquals($path, $this->container['project_directory']);
        $this->assertEquals($path.DIRECTORY_SEPARATOR.'.dancer.yml', $this->container['config_file']);
    }

    /** @test */
    public function it_allows_the_config_file_as_command_option()
    {
        $this->container['current_dir'] = $this->mkDir('org2/my-project/sub');
        $path = dirname(dirname($this->container['current_dir']));
        touch($configFile = $this->container['current_dir'].DIRECTORY_SEPARATOR.'.dancer.yml.dist');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->listener->__invoke($this->createEvent($path, $configFile));

        $this->assertArrayHasKey('project_directory', $this->container);
        $this->assertEquals($path, $this->container['project_directory']);
        $this->assertEquals($configFile, $this->container['config_file']);
    }

    /** @test */
    public function it_allows_to_disable_the_config_file()
    {
        $this->container['current_dir'] = $this->mkDir('org2/my-project/sub');
        $path = dirname(dirname($this->container['current_dir']));
        touch($path.'/.dancer.yml');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->listener->__invoke($this->createEvent($path, null));

        $this->assertArrayHasKey('project_directory', $this->container);
        $this->assertEquals($path, $this->container['project_directory']);
        $this->assertNull($this->container['config_file']);
    }

    /** @test */
    public function it_errors_when_the_config_file_is_provided_but_does_not_exist()
    {
        $this->container['current_dir'] = $this->mkDir('org2/my-project/sub');
        $path = dirname(dirname($this->container['current_dir']));

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unable to load configuration file, no such file. '.
            'Searched in the following locations: "if-this-exists-I-will-eat-my-shoos.DEF6A1.yml".'
        );

        $this->listener->__invoke($this->createEvent($path, 'if-this-exists-I-will-eat-my-shoos.DEF6A1.yml'));
    }

    /** @test */
    public function it_errors_when_the_provided_project_directory_does_not_exist()
    {
        $this->container['current_dir'] = $this->mkDir('org2/my-project/sub');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Project directory "/if-this-exists-I-will-eat-my-shoos.DEF6A1'
        );

        $this->listener->__invoke($this->createEvent('/if-this-exists-I-will-eat-my-shoos.DEF6A1'));
    }

    /** @test */
    public function it_errors_when_the_provided_project_directory_does_not_belong_to_current_directory()
    {
        $this->container['current_dir'] = $this->mkDir('org2/my-project/sub');
        $projectDir = $this->mkDir('org/my-project/sub');

        $this->assertArrayNotHasKey('project_directory', $this->container);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The current directory "%s" does not belong to the project-directory "%s"',
                $this->container['current_dir'],
                $projectDir
            )
        );

        $this->listener->__invoke($this->createEvent($projectDir));
    }

    private function mkDir($path)
    {
        $dir = $this->tmpFs.'/'.$path;

        if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Unable to create folder "%s".', $dir));
        }

        return realpath($dir);
    }

    private function createEvent($dir = '', $filename = '')
    {
        $args = '';

        if ($dir) {
            $args .= "--project-directory='$dir'";
        }

        if ($filename) {
            $args .= " --config-file='$filename'";
        }

        return new PreHandleEvent(
            (new Args($this->commandConfig->buildArgsFormat(), new StringArgs($args)))
                ->setOption('project-directory', $dir)
                ->setOption('config-file', $filename),
            $this->io,
            new Command($this->commandConfig)
        );
    }
}
