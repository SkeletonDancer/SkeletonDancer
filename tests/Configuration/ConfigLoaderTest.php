<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Tests\Configuration;

use Rollerworks\Tools\SkeletonDancer\Configuration\ConfigLoader;

final class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigLoader
     */
    private $configLoader;

    protected function setUp()
    {
        $this->configLoader = new ConfigLoader(__DIR__.'/../Fixtures/Project1/.dancer');
    }

    /** @test */
    public function it_loads_configuration_from_a_file()
    {
        $config = $this->configLoader->processFiles(['config.yml']);

        $this->assertArrayHasKey('defaults', $config);
        $this->assertEquals(
            [
                'name' => 'Rollerworks SearchBundle',
                'doc_format2' => 'rst',
                'doc_format' => '@doc_format2',
            ],
            $config['defaults']
        );
    }

    /** @test */
    public function it_loads_configuration_from_an_empty_file()
    {
        $config = $this->configLoader->processFiles(['empty.yml']);

        $this->assertEquals(
            [
                'defaults' => [],
                'overwrite' => 'ask',
                'profiles' => [],
            ],
            $config
        );
    }

    /** @test */
    public function it_loads_configuration_from_imported_files()
    {
        $config = $this->configLoader->processFiles(['imports.yml']);

        $this->assertEquals(
            [
                'bar' => 'car',
                'name' => 'Rollerworks SearchBundle',
                'bla' => 'who',
                'doc_format2' => 'rst',
                'doc_format' => '@doc_format2',
            ],
            $config['defaults']
        );
    }

    /** @test */
    public function it_throws_an_exception_when_recursion_is_detected()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp(
            '{File "(.+?)/tests/Fixtures/Project1/\.dancer/invalid_imports\.yml" is already being '.
            'loaded with the following order: "(.+?)/tests/Fixtures/Project1/\.dancer/invalid_imports\.yml", '.
            '"(.+?)/tests/Fixtures/Project1/\.dancer/Config/file3\.yml"}'
        );

        $this->configLoader->processFiles(['invalid_imports.yml']);
    }

    /** @test */
    public function it_throws_an_exception_when_file_content_is_not_an_array()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp(
            '{Expected file "(.+?)tests/Fixtures/Project1/\.dancer/invalid\.yml" to contain an array structure.}'
        );

        $this->configLoader->processFiles(['invalid.yml']);
    }
}
