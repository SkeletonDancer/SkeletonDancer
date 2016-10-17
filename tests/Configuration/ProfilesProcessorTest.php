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

use Rollerworks\Tools\SkeletonDancer\Configuration\ProfilesProcessor;

final class ProfilesProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProfilesProcessor
     */
    private $processor;

    protected function setUp()
    {
        $this->processor = new ProfilesProcessor();
    }

    /** @test */
    public function it_processes_profiles_with_no_imports()
    {
        $this->assertEquals(
            [
                'first' => [
                    'generators' => ['one1', 'two1'],
                    'configurators' => [],
                    'description' => 'Foo',
                    'import' => [],
                    'defaults' => [],
                ],
                'second' => [
                    'generators' => ['one2', 'two2'],
                    'configurators' => [],
                    'description' => 'Bar',
                    'import' => [],
                    'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'],
                ],
            ],
            $this->processor->process(
                [
                    'first' => [
                        'generators' => ['one1', 'two1'],
                        'description' => 'Foo',
                        'import' => [],
                        'defaults' => [],
                    ],
                    'second' => [
                        'generators' => ['one2', 'two2'],
                        'description' => 'Bar',
                        'import' => [],
                        'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'],
                    ],
                ]
            )
        );
    }

    /** @test */
    public function it_merges_profile_imports_into_the_current()
    {
        $this->assertEquals(
            [
                'first' => [
                    'generators' => ['one2', 'two2', 'two1', 'one1'],
                    'configurators' => [],
                    'description' => 'Foo',
                    'import' => ['second'],
                    'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something', 'he' => 'you'],
                ],
                'second' => [
                    'generators' => ['one2', 'two2', 'two1'],
                    'configurators' => [],
                    'description' => 'Bar',
                    'import' => [],
                    'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'],
                ],
            ],
            $this->processor->process(
                [
                    'first' => [
                        'generators' => ['one1', 'two1'],
                        'description' => 'Foo',
                        'import' => ['second'],
                        'defaults' => ['he' => 'you'],
                    ],
                    'second' => [
                        'generators' => ['one2', 'two2', 'two1'],
                        'description' => 'Bar',
                        'import' => [],
                        'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'],
                    ],
                ]
            )
        );
    }

    /** @test */
    public function it_throws_an_exception_when_imported_profile_is_unregistered()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to import unregistered profile "second" for "first".');

        $this->processor->process(
            [
                'first' => [
                    'generators' => ['one1', 'two1'],
                    'description' => 'Foo',
                    'import' => ['second'],
                    'defaults' => ['he' => 'you'],
                ],
            ]
        );
    }

    /** @test */
    public function it_throws_an_exception_when_imported_profile_is_already_loading()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Profile "first" is already being imported by: "first" -> "second" -> "third".');

        $this->processor->process(
            [
                'first' => [
                    'generators' => ['one1', 'two1'],
                    'description' => 'Foo',
                    'import' => ['second'],
                ],
                'second' => [
                    'generators' => ['one2', 'two2', 'two1'],
                    'description' => 'Bar',
                    'import' => ['third'],
                ],
                'third' => [
                    'generators' => ['one2', 'two2', 'two1'],
                    'description' => 'Bar',
                    'import' => ['first'],
                ],
            ]
        );
    }
}
