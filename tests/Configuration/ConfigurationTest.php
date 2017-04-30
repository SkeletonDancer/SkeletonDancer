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

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Rollerworks\Tools\SkeletonDancer\Configuration\Configuration;

final class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    /** @test */
    public function it_merges_defaults()
    {
        $this->assertProcessedConfigurationEquals(
            [
                ['defaults' => ['bar' => 'foo', 'bla' => 'who']],
                ['defaults' => ['bla' => 'poo', 'sum' => 'something']],
            ],
            [
                'variables' => [],
                'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'],
                'overwrite' => 'ask',
                'profiles' => [],
            ]
        );
    }

    /** @test */
    public function it_merges_profiles_with_merging_only_defaults()
    {
        $this->assertProcessedConfigurationEquals(
            [
                [
                    'profiles' => [
                        'first' => ['generators' => ['one1', 'two1']],
                        'second' => [
                            'generators' => ['one', 'two'],
                            'defaults' => ['bar' => 'foo', 'bla' => 'who'],
                        ],
                    ],
                ],
                [
                    'profiles' => [
                        'second' => [
                            'generators' => ['one2', 'two2'],
                            'defaults' => ['bla' => 'poo', 'sum' => 'something'],
                        ],
                    ],
                ],
            ],
            [
                'variables' => [],
                'defaults' => [],
                'overwrite' => 'ask',
                'profiles' => [
                    'first' => [
                        'generators' => ['one1', 'two1'],
                        'configurators' => [],
                        'description' => '',
                        'variables' => [],
                        'defaults' => [],
                    ],
                    'second' => [
                        'generators' => ['one2', 'two2'],
                        'configurators' => [],
                        'description' => '',
                        'variables' => [],
                        'defaults' => ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'],
                    ],
                ],
            ]
        );
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
