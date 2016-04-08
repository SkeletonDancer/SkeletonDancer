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

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\DefaultsProcessor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class DefaultsProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DefaultsProcessor
     */
    private $processor;

    /**
     * @before
     */
    public function setUpProcessor()
    {
        $this->config = new Config(
            [
                'defaults' => [
                    'foo' => 'dar',
                    'bla' => 'thing',
                ],
                'profiles' => [
                    'my-profile' => [
                        'defaults' => [
                            'foo' => 'bar',
                            'some' => 'thing',
                            'he' => '@foo',
                            'car' => '@@foo',
                            'car2' => '@@@foo',
                        ],
                    ],
                ],
            ]
        );

        $this->processor = new DefaultsProcessor(new ExpressionLanguage(), $this->config);
    }

    /** @test */
    public function it_processes_a_profile()
    {
        $this->assertEquals(
            [
                'bla' => 'thing',
                'foo' => 'bar',
                'some' => 'thing',
                'he' => 'bar',
                'car' => '@foo',
                'car2' => '@@foo',
            ],
            $this->processor->process('my-profile')
        );
    }
}
