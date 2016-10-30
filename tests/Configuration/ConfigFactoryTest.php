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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Rollerworks\Tools\SkeletonDancer\Configuration\ConfigFactory;
use Rollerworks\Tools\SkeletonDancer\Profile;

class ConfigFactoryTest extends TestCase
{
    /** @test */
    public function it_loads_with_minimum_configuration()
    {
        $path = 'Fixtures/Project2/empty';
        $config = (new ConfigFactory(__DIR__.'/../'.$path, __DIR__.'/../'.$path))->create();

        self::assertEquals('ask', $config->get('overwrite'));
        self::assertEquals([], $config->get('variables'));
        self::assertEquals([], $config->get('defaults'));
        self::assertNull($config->get('config_file'));
        self::assertNull($config->get('dancer_directory'));
        self::assertStringEndsWith($path, $config->get('project_directory'));
        self::assertStringEndsWith($path, $config->get('current_dir'));
        self::assertEquals('empty', $config->get('current_dir_name'));

        $profiles = $config->getProfiles();

        self::assertArrayHasKey('dancer-init', $profiles);
        self::assertInstanceOf(Profile::class, $profiles['dancer-init']);
    }

    /** @test */
    public function it_loads_with_configuration_with_dancer_directory()
    {
        $path = 'Fixtures/Project1';
        $pathR = __DIR__.'/../'.$path;

        $config = (new ConfigFactory($pathR.'/new', $pathR))->setDancerDirectory($pathR.'/.dancer')->create();

        self::assertEquals('ask', $config->get('overwrite'));
        self::assertEquals([], $config->get('variables'));
        self::assertEquals([], $config->get('defaults'));
        self::assertNull($config->get('config_file'));
        self::assertStringEndsWith($path.'/.dancer', $config->get('dancer_directory'));
        self::assertStringEndsWith($path, $config->get('project_directory'));
        self::assertStringEndsWith($path.'/new', $config->get('current_dir'));
        self::assertEquals('new', $config->get('current_dir_name'));

        $profiles = $config->getProfiles();

        self::assertArrayHasKey('dancer-init', $profiles);
        self::assertInstanceOf(Profile::class, $profiles['dancer-init']);
    }

    /** @test */
    public function it_loads_with_configuration_with_config_file()
    {
        $path = 'Fixtures/Project1';
        $pathR = __DIR__.'/../'.$path;

        $config = (new ConfigFactory($pathR.'/new', $pathR))->setConfigFile($pathR.'/.dancer/config.yml')->create();

        self::assertEquals('ask', $config->get('overwrite'));
        self::assertEquals([], $config->get('variables'));
        self::assertEquals(
            [
                'name' => 'Rollerworks SearchBundle',
                'doc_format2' => 'rst',
                'doc_format' => '@doc_format2',
            ],
            $config->get('defaults')
        );
        self::assertStringEndsWith($path.'/.dancer/config.yml', $config->get('config_file'));
        self::assertNull($config->get('dancer_directory'));
        self::assertStringEndsWith($path, $config->get('project_directory'));
        self::assertStringEndsWith($path.'/new', $config->get('current_dir'));
        self::assertEquals('new', $config->get('current_dir_name'));

        $profiles = $config->getProfiles();

        self::assertArrayHasKey('dancer-init', $profiles);
        self::assertInstanceOf(Profile::class, $profiles['dancer-init']);
    }

    /** @test */
    public function it_allows_setting_overwrite_setting()
    {
        $path = 'Fixtures/Project2/empty';
        $config = (new ConfigFactory(__DIR__.'/../'.$path, __DIR__.'/../'.$path))->setFileOverwrite('force')->create();

        self::assertEquals('force', $config->get('overwrite'));
        self::assertEquals([], $config->get('variables'));
        self::assertEquals([], $config->get('defaults'));
        self::assertNull($config->get('config_file'));
        self::assertNull($config->get('dancer_directory'));
        self::assertStringEndsWith($path, $config->get('project_directory'));
        self::assertStringEndsWith($path, $config->get('current_dir'));
        self::assertEquals('empty', $config->get('current_dir_name'));

        $profiles = $config->getProfiles();

        self::assertArrayHasKey('dancer-init', $profiles);
        self::assertInstanceOf(Profile::class, $profiles['dancer-init']);
    }
}
