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

namespace Rollerworks\Tools\SkeletonDancer\Tests\Generator;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Rollerworks\Tools\SkeletonDancer\Service\Composer;

final class ComposerGeneratorTest extends GeneratorTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $composer;

    /**
     * @before
     */
    public function setUpComposer()
    {
        $this->composer = $this->prophesize(Composer::class);
        $this->container['composer'] = function () {
            return $this->composer->reveal();
        };
    }

    /**
     * @test
     */
    public function it_generates_a_composer_file()
    {
        $this->composer->requirePackage([])->shouldBeCalled();
        $this->composer->requireDevPackage([])->shouldBeCalled();

        $this->filesystem->dumpFile(
            'composer.json',
            Argument::that(
                $this->createJsonArgumentAssertion(
                    [
                        'name' => 'rollerworks/skeleton-dancer',
                        'description' => '',
                        'type' => 'library',
                        'license' => 'MIT',
                        'authors' => [
                            [
                                'name' => 'Sebastiaan Stok',
                                'email' => 's.stok@rollerscapes.net',
                            ],
                        ],
                        'require' => [
                            'php' => '^5.5',
                        ],
                        'require-dev' => [],
                        'autoload' => [
                            'psr-4' => [
                                'Rollerworks\\Tools\\SkeletonDancer\\' => 'src',
                            ],
                        ],
                    ]
                )
            )
        )->shouldBeCalled();

        $this->runGenerator(
            [
                'name' => 'SkeletonDancer',
                'package_name' => 'rollerworks/skeleton-dancer',
                'namespace' => 'Rollerworks\\Tools\\SkeletonDancer',
                'author_name' => 'Sebastiaan Stok',
                'author_email' => 's.stok@rollerscapes.net',
                'php_min' => '5.5',
                'composer_prefer_stable' => true,
            ]
        );
    }

    /**
     * @test
     */
    public function it_generates_a_composer_file_and_performs_requirements_using_service()
    {
        $this->composer->requirePackage(['symfony/symfony'])->shouldBeCalled();
        $this->composer->requireDevPackage([])->shouldBeCalled();

        $this->filesystem->dumpFile(
            'composer.json',
            Argument::that(
                $this->createJsonArgumentAssertion(
                    [
                        'name' => 'rollerworks/skeleton-dancer',
                        'description' => '',
                        'type' => 'library',
                        'license' => 'MIT',
                        'authors' => [
                            [
                                'name' => 'Sebastiaan Stok',
                                'email' => 's.stok@rollerscapes.net',
                            ],
                        ],
                        'require' => [
                            'php' => '^5.5',
                        ],
                        'require-dev' => [],
                        'autoload' => [
                            'psr-4' => [
                                'Rollerworks\\Tools\\SkeletonDancer\\' => 'src',
                            ],
                        ],
                    ]
                )
            )
        )->shouldBeCalled();

        $this->runGenerator(
            [
                'name' => 'SkeletonDancer',
                'package_name' => 'rollerworks/skeleton-dancer',
                'namespace' => 'Rollerworks\\Tools\\SkeletonDancer',
                'author_name' => 'Sebastiaan Stok',
                'author_email' => 's.stok@rollerscapes.net',
                'php_min' => '5.5',
            ],
            [
                'composer' => ['require' => ['symfony/symfony']],
            ]
        );
    }

    /**
     * @test
     */
    public function it_generates_a_composer_file_and_allows_extra_data()
    {
        $this->composer->requirePackage([])->shouldBeCalled();
        $this->composer->requireDevPackage([])->shouldBeCalled();

        $this->filesystem->dumpFile(
            'composer.json',
            Argument::that(
                $this->createJsonArgumentAssertion(
                    [
                        'name' => 'rollerworks/skeleton-dancer',
                        'description' => '',
                        'type' => 'library',
                        'license' => 'MIT',
                        'authors' => [
                            [
                                'name' => 'Sebastiaan Stok',
                                'email' => 's.stok@rollerscapes.net',
                            ],
                        ],
                        'require' => [
                            'php' => '^5.5',
                        ],
                        'require-dev' => [],
                        'autoload' => [
                            'psr-4' => [
                                'Rollerworks\\Tools\\SkeletonDancer\\' => 'src',
                            ],
                        ],
                        'minimum-stability' => 'dev',
                    ]
                )
            )
        )->shouldBeCalled();

        $this->runGenerator(
            [
                'name' => 'SkeletonDancer',
                'package_name' => 'rollerworks/skeleton-dancer',
                'namespace' => 'Rollerworks\\Tools\\SkeletonDancer',
                'author_name' => 'Sebastiaan Stok',
                'author_email' => 's.stok@rollerscapes.net',
                'php_min' => '5.5',
            ],
            [
                'composer' => ['minimum-stability' => 'dev'],
            ]
        );
    }

    /**
     * @test
     */
    public function it_generates_a_composer_file_and_with_extra_options()
    {
        $this->composer->requirePackage([])->shouldBeCalled();
        $this->composer->requireDevPackage([])->shouldBeCalled();

        $this->filesystem->dumpFile(
            'composer.json',
            Argument::that(
                $this->createJsonArgumentAssertion(
                    [
                        'name' => 'rollerworks/skeleton-dancer',
                        'description' => '',
                        'type' => 'library',
                        'license' => 'MIT',
                        'authors' => [
                            [
                                'name' => 'Sebastiaan Stok',
                                'email' => 's.stok@rollerscapes.net',
                            ],
                        ],
                        'require' => [
                            'php' => '^5.5',
                        ],
                        'require-dev' => [],
                        'autoload' => [
                            'psr-4' => [
                                'Rollerworks\\Tools\\SkeletonDancer\\' => 'src',
                            ],
                        ],
                        'minimum-stability' => 'dev',
                        'prefer-stable' => true,
                    ]
                )
            )
        )->shouldBeCalled();

        $this->runGenerator(
            [
                'name' => 'SkeletonDancer',
                'package_name' => 'rollerworks/skeleton-dancer',
                'namespace' => 'Rollerworks\\Tools\\SkeletonDancer',
                'author_name' => 'Sebastiaan Stok',
                'author_email' => 's.stok@rollerscapes.net',
                'php_min' => '5.5',
                'composer_minimum_stability' => 'dev',
                'composer_prefer_stable' => true,
            ]
        );
    }

    private function createJsonArgumentAssertion($expected)
    {
        return function ($composer) use ($expected) {
            $this->assertJson($composer);
            $this->assertEquals($expected, json_decode($composer, true));

            return true;
        };
    }
}
