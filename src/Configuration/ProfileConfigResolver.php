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
    /**
     * @var Profile[]
     */
    private $profiles;

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

        $rootProfile = clone $this->profiles[$profile];
        $rootProfile->variables = array_merge($this->variables, $this->profiles[$profile]->variables);
        $rootProfile->defaults = array_merge($this->defaults, $this->profiles[$profile]->defaults);

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
}
