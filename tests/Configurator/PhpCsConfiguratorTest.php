<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Tests\Configurator;

final class PhpCsConfiguratorTest extends ConfiguratorTestCase
{
    /**
     * @test
     * @dataProvider getFinderValues
     */
    public function it_splits_finder_options($name, $value, $expected)
    {
        $result = $this->runConfigurator(['php_cs_finder_'.$name => $value]);

        $this->assertArrayHasKey('php_cs_finder', $result);
        $this->assertArrayHasKey($name, $result['php_cs_finder']);
        $this->assertEquals($expected, $result['php_cs_finder'][$name]);
    }

    /** @test */
    public function it_removes_overlapping_fixer_with_style_ci_bridge_enabled()
    {
        $result = $this->runConfigurator(
            [
                'php_cs_styleci_bridge' => true,
                'php_cs_enabled_fixers' => 'braces,psr4,ereg_to_preg',
                'php_cs_preset' => 'laravel',
            ]
        );

        self::assertArrayHasKeyAndValueEquals('php_cs_preset', $result, 'laravel');
        self::assertArrayHasKeyAndValueEquals('php_cs_level', $result, 'laravel');

        self::assertArrayHasKeyAndArrayValuesEquals('php_cs_enabled_fixers', $result, ['ereg_to_preg']);
        self::assertArrayHasKeyAndValueEquals('php_cs_enabled_fixers_v1', $result, []);
        self::assertArrayHasKeyAndValueEquals('php_cs_disabled_fixers_v1', $result, []);
    }

    /** @test */
    public function it_removes_overlapping_fixer_with_style_ci_bridge_disabled()
    {
        $result = $this->runConfigurator(
            [
                'php_cs_styleci_bridge' => false,
                'php_cs_enabled_fixers' => 'braces,psr4,ereg_to_preg',
                'php_cs_preset' => 'laravel',
            ]
        );

        self::assertArrayHasKeyAndValueEquals('php_cs_preset', $result, 'laravel');
        self::assertArrayHasKeyAndValueEquals('php_cs_level', $result, 'none');

        self::assertArrayHasKeyAndArrayValuesEquals('php_cs_enabled_fixers', $result, ['braces', 'psr4', 'ereg_to_preg']);
        self::assertArrayHasKeyAndValueEquals('php_cs_enabled_fixers_v1', $result, []);
        self::assertArrayHasKeyAndValueEquals('php_cs_disabled_fixers_v1', $result, []);

        $result = $this->runConfigurator(
            [
                'php_cs_styleci_bridge' => false,
                'php_cs_enabled_fixers' => 'braces,psr4,ereg_to_preg',
                'php_cs_preset' => 'symfony',
            ]
        );

        self::assertArrayHasKeyAndValueEquals('php_cs_preset', $result, 'symfony');
        self::assertArrayHasKeyAndValueEquals('php_cs_level', $result, 'symfony');

        self::assertArrayHasKeyAndArrayValuesEquals('php_cs_enabled_fixers', $result, ['ereg_to_preg']);
        self::assertArrayHasKeyAndValueEquals('php_cs_enabled_fixers_v1', $result, []);
        self::assertArrayHasKeyAndValueEquals('php_cs_disabled_fixers_v1', $result, []);
    }

    public function getFinderValues()
    {
        return [
            'path' => ['path', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
            'not_path' => ['not_path', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
            'exclude' => ['exclude', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
            'name' => ['name', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
            'not_name' => ['not_name', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
            'contains' => ['contains', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
            'not_contains' => ['not_contains', 'foo.php,bar.php , src/bla.php', ['foo.php', 'bar.php', 'src/bla.php']],
        ];
    }
}
