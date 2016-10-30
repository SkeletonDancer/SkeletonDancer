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

use Rollerworks\Tools\SkeletonDancer\Configuration\AutomaticProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Profile;
use Rollerworks\Tools\SkeletonDancer\Tests\Mocks\ProfileResolverClass;

final class AutomaticProfileResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_resolves_using_a_pattern_map()
    {
        $resolver = $this->createProfileResolver(
            [
                'src/Bundle/' => 'bundle',
                'src\/Component/Foo' => 'foo', // should be ignored
                '#src/component#i' => 'library', // should be matched as regex
            ]
        );

        self::assertEquals('bundle', $resolver->resolve()->name);

        $resolver = $this->createProfileResolver(
            [
                'src/Bundle/' => 'bundle',
                'src\/Component/Foo' => 'foo', // should be ignored
                '#src/component#i' => 'library', // should be matched as regex
            ],
            'src/Component/MyComponent'
        );

        self::assertEquals('library', $resolver->resolve()->name);
    }

    /** @test */
    public function it_resolves_using_a_custom_class()
    {
        $resolver = $this->createProfileResolver(
            ProfileResolverClass::class
        );

        self::assertEquals('bundle', $resolver->resolve()->name);

        $resolver = $this->createProfileResolver(
            ProfileResolverClass::class,
            'src/Component/MyComponent'
        );

        self::assertEquals('library', $resolver->resolve()->name);
    }

    /** @test */
    public function it_throws_an_exception_when_passed_profile_is_unregistered()
    {
        $resolver = $this->createProfileResolver();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Profile "foo" is not registered, please use one of the following: "bundle", "library".'
        );

        $resolver->resolve('foo');
    }

    /** @test */
    public function it_throws_an_exception_when_resolved_profile_is_unregistered()
    {
        $resolver = $this->createProfileResolver(['src/' => 'foo']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Profile "foo" returned by the profile resolver is not registered, please check your configuration.'
        );

        $resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_no_resolver_is_configured()
    {
        $resolver = $this->createProfileResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to automatically resolve the correct profile, no profile resolver was configured. '.
            'Provide the profile manually.'
        );

        $resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_invalid_class_resolver_was_configured()
    {
        $resolver = $this->createProfileResolver('ThisWill-Never=Exists');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to automatically resolve the correct profile, invalid profile resolver configured. '.
            'No such class: "ThisWill-Never=Exists".'
        );

        $resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_class_resolver_with_missing_method_was_configured()
    {
        $resolver = $this->createProfileResolver(\stdClass::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to automatically resolve the correct profile, invalid profile resolver configured. '.
            'Method resolve() does not exist in class "stdClass".'
        );

        $resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_no_profile_was_resolved()
    {
        $resolver = $this->createProfileResolver(['Something' => 'bundle']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Was unable to automatically resolve the correct profile, profile resolver did not return a result. '.
            'Please check your configuration/resolver or provide the profile manually.'
        );

        $resolver->resolve();
    }

    private function createProfileResolver(
        $resolver = null,
        $relativeDir = 'src/Bundle/MyBundle'
    ): AutomaticProfileResolver {
        $config = new Config(
            ['profile_resolver' => $resolver, 'current_dir_relative' => $relativeDir],
            ['bundle' => new Profile('bundle'), 'library' => new Profile('library')]
        );

        return new AutomaticProfileResolver($config);
    }
}
