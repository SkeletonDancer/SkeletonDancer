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

namespace Rollerworks\Tools\SkeletonDancer\Configuration;

use Rollerworks\Tools\SkeletonDancer\Profile;
use Rollerworks\Tools\SkeletonDancer\ResolvedProfile;

final class ProfileConfigResolver
{
    private $profiles;
    private $loading = [];

    /**
     * @var ClassLoader
     */
    private $loader;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var array
     */
    private $defaults;

    /**
     * ProfileConfigResolver constructor.
     *
     * @param Profile[]   $profiles
     * @param ClassLoader $loader
     * @param array       $variables
     * @param array       $defaults
     */
    public function __construct(array $profiles, ClassLoader $loader, array $variables = [], array $defaults = [])
    {
        $this->profiles = $profiles;
        $this->loader = $loader;
        $this->variables = $variables;
        $this->defaults = $defaults;
    }

    /**
     * @param string $profile
     *
     * @return ResolvedProfile
     */
    public function resolve(string $profile): ResolvedProfile
    {
        if (!isset($this->profiles[$profile])) {
            throw new \InvalidArgumentException(sprintf('Unable to resolve unregistered profile "%s".', $profile));
        }

        $this->loader->clear();
        $this->loading = [$profile];

        $rootProfile = new Profile($profile, [], [], [], $this->variables, $this->defaults);
        $this->processProfile($rootProfile, $this->profiles[$profile]);
        $this->loading = [];

        $this->loader->loadGeneratorClasses(array_unique($rootProfile->generators));
        $this->loader->loadConfiguratorClasses(array_unique($rootProfile->configurators));

        return new ResolvedProfile(
            $profile,
            $this->loader->getGenerators(),
            $this->loader->getConfigurators(),
            $rootProfile->variables,
            $rootProfile->defaults
        );
    }

    private function processProfile(Profile $base, Profile $profile)
    {
        if (count($profile->imports) > 0) {
            $this->processImports($base, $profile->name, $profile->imports);
        }

        $base->generators = array_merge($base->generators, $profile->generators);
        $base->configurators = array_merge($base->configurators, $profile->configurators);
        $base->variables = array_merge($base->variables, $profile->variables);
        $base->defaults = array_merge($base->defaults, $profile->defaults);
    }

    private function processImports(Profile $base, $name, array $imports)
    {
        foreach ($imports as $import) {
            if (in_array($import, $this->loading, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Profile "%s" is already being imported by: "%s".',
                        $import,
                        implode('" -> "', $this->loading)
                    )
                );
            }

            if (!isset($this->profiles[$import])) {
                throw new \InvalidArgumentException(
                    sprintf('Unable to import unregistered profile "%s" for "%s".', $import, $name)
                );
            }

            $this->loading[] = $import;

            $this->processProfile($base, $this->profiles[$import]);
        }
    }
}
