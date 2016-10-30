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

use Rollerworks\Tools\SkeletonDancer\Configuration\ProfileConfigResolver;
use Rollerworks\Tools\SkeletonDancer\Profile;
use Rollerworks\Tools\SkeletonDancer\ResolvedProfile;
use Rollerworks\Tools\SkeletonDancer\Tests\Mocks\ClassLoaderMock;

final class ProfileConfigResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_processes_profiles_with_no_imports()
    {
        $profiles = [
            'first' => new Profile('first', ['one1', 'two1']),
            'second' => new Profile(
                'second',
                ['one2', 'two2'],
                [],
                [],
                [],
                ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'] // defaults
            ),
            'third' => new Profile(
                'third',
                ['one2', 'two2'],
                ['conf1', 'conf2'],
                [],
                ['foo' => 'boo', '_peep' => 'bong'],
                ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'] // defaults
            ),
        ];

        $resolver = $this->createResolver($profiles);

        self::assertEquals(new ResolvedProfile('first', ['one1', 'two1']), $resolver->resolve('first'));
        self::assertEquals(
            new ResolvedProfile(
                'second',
                ['one2', 'two2'],
                [],
                [],
                ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'] // defaults
            ),
            $resolver->resolve('second')
        );

        self::assertEquals(
            new ResolvedProfile(
                'third',
                ['one2', 'two2'],
                ['conf1', 'conf2'],
                ['foo' => 'boo', '_peep' => 'bong'],
                ['bar' => 'foo', 'bla' => 'poo', 'sum' => 'something'] // defaults
            ),
            $resolver->resolve('third')
        );
    }

    /** @test */
    public function it_merges_global_vars_and_defaults_into_the_current()
    {
        $profiles = [
            'first' => new Profile(
                'first',
                ['one1', 'two1'], // generator
                [], // configurators
                [], // imports
                ['he' => 'you', 'sum' => 'some', '_me' => 'foo'], // variables
                ['last' => 'we', 'name' => 'who'] // defaults
            ),
        ];

        $resolver = $this->createResolver(
            $profiles,
            ['bar' => 'foo', '_me' => 'bar', 'bla' => 'poo', 'sum' => 'something'],
            ['name' => 'doc', 'age' => 999]
        );

        self::assertEquals(
            new ResolvedProfile(
                'first',
                ['one1', 'two1'], // generator
                [], // configurators
                ['bar' => 'foo', '_me' => 'foo', 'bla' => 'poo', 'sum' => 'some', 'he' => 'you'], // variables
                ['name' => 'who', 'last' => 'we', 'age' => 999] // defaults
            ),
            $resolved = $resolver->resolve('first')
        );
    }

    /** @test */
    public function it_merges_profile_imports_into_the_current()
    {
        $profiles = [
            'first' => new Profile(
                'first',
                ['one1', 'two1'], // generator
                [], // configurators
                ['second'], // imports
                ['he' => 'you', 'sum' => 'some', '_me' => 'foo'], // variables
                ['last' => 'we', 'name' => 'who'] // defaults
            ),
            'second' => new Profile(
                'second',
                ['one2', 'two2', 'two1'],
                [],
                [],
                ['bar' => 'foo', '_me' => 'bar', 'bla' => 'poo', 'sum' => 'something'],
                ['name' => 'doc', 'age' => 999]
            ),
        ];

        $resolver = $this->createResolver($profiles);

        self::assertEquals(
            new ResolvedProfile(
                'first',
                ['one2', 'two2', 'two1', 'one1'], // generator
                [], // configurators
                ['bar' => 'foo', '_me' => 'foo', 'bla' => 'poo', 'sum' => 'some', 'he' => 'you'], // variables
                ['name' => 'who', 'last' => 'we', 'age' => 999] // defaults
            ),
            $resolved = $resolver->resolve('first')
        );

        self::assertEquals(
            new ResolvedProfile(
                'second',
                ['one2', 'two2', 'two1'], // generator
                [], // configurators
                ['bar' => 'foo', '_me' => 'bar', 'bla' => 'poo', 'sum' => 'something'], // variables
                ['name' => 'doc', 'age' => 999] // defaults
            ),
            $resolver->resolve('second')
        );
    }

    /** @test */
    public function it_throws_an_exception_when_imported_profile_is_unregistered()
    {
        $profiles = [
            'first' => new Profile(
                'first',
                ['one1', 'two1'], // generator
                [], // configurators
                ['second'], // imports
                ['he' => 'you', 'sum' => 'some', '_me' => 'foo'], // variables
                ['last' => 'we', 'name' => 'who'] // defaults
            ),
        ];

        $resolver = $this->createResolver($profiles);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to import unregistered profile "second" for "first".');

        $resolver->resolve('first');
    }

    /** @test */
    public function it_throws_an_exception_when_imported_profile_is_already_loading()
    {
        $profiles = [
            'first' => new Profile(
                'first',
                ['one1', 'two1'], // generator
                [], // configurators
                ['second'] // imports
            ),
            'second' => new Profile(
                'second',
                ['one2', 'two2', 'two1'],
                [],
                ['third']
            ),
            'third' => new Profile(
                'second',
                ['one2', 'two2', 'two1'],
                [],
                ['first']
            ),
        ];

        $resolver = $this->createResolver($profiles);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Profile "first" is already being imported by: "first" -> "second" -> "third".');

        $resolver->resolve('first');
    }

    private function createResolver(array $profiles, array $variables = [], array $defaults = []): ProfileConfigResolver
    {
        return new ProfileConfigResolver($profiles, new ClassLoaderMock(), $variables, $defaults);
    }
}
