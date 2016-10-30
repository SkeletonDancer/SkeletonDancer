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

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;

final class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    protected function setUp()
    {
        $this->config = new Config(
            [
                'profile' => 'default',
                'bla' => [
                    'foo' => 'something',
                    'bar' => 'boo',
                    'nil' => null,
                ],
                'current_dir' => '/var/tmp/my-project',
                'null_value' => null,
            ],
            ['current_dir']
        );
    }

    /** @test */
    public function it_allows_getting_values()
    {
        $this->assertTrue($this->config->has('profile'));
        $this->assertTrue($this->config->has('current_dir'));
        $this->assertTrue($this->config->has('bla'));
        $this->assertTrue($this->config->has('null_value'));
        $this->assertFalse($this->config->has('foo'));
        $this->assertFalse($this->config->has(['profile', 'wat']));

        $this->assertEquals('default', $this->config->get('profile'));
        $this->assertNull($this->config->get('null_value', 'nope'));
        $this->assertEquals('/var/tmp/my-project', $this->config->get(['current_dir']));
        $this->assertEquals(
            [
                'foo' => 'something',
                'bar' => 'boo',
                'nil' => null,
            ],
            $this->config->get('bla')
        );

        // Deeper level.
        $this->assertTrue($this->config->has(['bla', 'foo']));
        $this->assertEquals('something', $this->config->get(['bla', 'foo']));
        $this->assertEquals('nope', $this->config->get(['bla', 'wat'], 'nope'));
        $this->assertNull($this->config->get(['bla', 'nil'], 'nope'));

        // Default value.
        $this->assertEquals('nope', $this->config->get('foo', 'nope'));
    }

    /** @test */
    public function it_allows_getting_first_non_null_value()
    {
        $this->assertEquals('/var/tmp/my-project', $this->config->getFirstNotNull(['current_dir', 'profile']));
        $this->assertEquals('boo', $this->config->getFirstNotNull([['bla', 'nil'], ['bla', 'bar']]));
        $this->assertEquals('nope', $this->config->getFirstNotNull([['bla', 'nil']], 'nope'));
    }
}
