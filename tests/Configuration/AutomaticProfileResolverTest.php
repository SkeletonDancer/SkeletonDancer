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

use Rollerworks\Tools\SkeletonDancer\Configuration\AutomaticProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;

final class AutomaticProfileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AutomaticProfileResolver
     */
    private $resolver;

    /**
     * @before
     */
    public function setUpResolver()
    {
        $this->config = new Config(['current_dir_relative' => 'src/Bundle/MyBundle']);
        $this->config->set('profiles', ['bundle' => [], 'library' => []]);

        $this->resolver = new AutomaticProfileResolver($this->config);
    }

    /** @test */
    public function it_resolves_using_a_pattern_map()
    {
        $this->config->set(
            'profile_resolver',
            [
                'src/Bundle/' => 'bundle',
                'src\/Component/Foo' => 'foo', // should be ignored
                '#src/component#i' => 'library', // should be matched as regex
            ]
        );

        $this->assertEquals('bundle', $this->resolver->resolve());

        $this->config->set('current_dir_relative', 'src/Component/MyComponent');

        $this->assertEquals('library', $this->resolver->resolve());
    }

    /** @test */
    public function it_resolves_using_a_custom_class()
    {
        $this->config->set(
            'profile_resolver',
            \Rollerworks\Tools\SkeletonDancer\Tests\Mocks\ProfileResolverClass::class
        );

        $this->assertEquals('bundle', $this->resolver->resolve());

        $this->config->set('current_dir_relative', 'src/Component/MyComponent');

        $this->assertEquals('library', $this->resolver->resolve());
    }

    /** @test */
    public function it_throws_an_exception_when_passed_profile_is_unregistered()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Profile "foo" is not registered, please use one of the following: "bundle", "library".'
        );

        $this->resolver->resolve('foo');
    }

    /** @test */
    public function it_throws_an_exception_when_resolved_profile_is_unregistered()
    {
        $this->config->set('profile_resolver', ['src/' => 'foo']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Profile "foo" returned by the profile resolver is not registered, please check your configuration.'
        );

        $this->resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_no_resolver_is_configured()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to automatically resolve the correct profile, no profile resolver was configured. '.
            'Provide the profile manually.'
        );

        $this->resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_invalid_class_resolver_was_configured()
    {
        $this->config->set('profile_resolver', 'ThisWill-Never=Exists');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to automatically resolve the correct profile, invalid profile resolver configured. '.
            'No such class: "ThisWill-Never=Exists".'
        );

        $this->resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_class_resolver_with_missing_method_was_configured()
    {
        $this->config->set('profile_resolver', \stdClass::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to automatically resolve the correct profile, invalid profile resolver configured. '.
            'Method resolve() does not exist in class "stdClass".'
        );

        $this->resolver->resolve();
    }

    /** @test */
    public function it_throws_an_exception_when_no_profile_was_resolved()
    {
        $this->config->set('profile_resolver', ['Something' => 'bundle']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Was unable to automatically resolve the correct profile, profile resolver did not return a result. '.
            'Please check your configuration/resolver or provide the profile manually.'
        );

        $this->resolver->resolve();
    }
}
