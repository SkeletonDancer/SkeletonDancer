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
use Symfony\Component\Console\Style\SymfonyStyle;

final class InteractiveProfileResolver implements ProfileResolver
{
    private $config;
    private $style;
    private $automaticResolver;

    public function __construct(
        Config $config,
        SymfonyStyle $style,
        AutomaticProfileResolver $automaticResolver
    ) {
        $this->config = $config;
        $this->style = $style;
        $this->automaticResolver = $automaticResolver;
    }

    public function resolve($profile = null): Profile
    {
        $profiles = $this->config->getProfiles();

        if (null !== $profile && !isset($profiles[$profile])) {
            $this->style->error(
                sprintf(
                    'Profile "%s" is not registered, please use one of the following: %s.',
                    $profile,
                    implode(', ', array_keys($profiles))
                )
            );

            $profile = null;
        }

        if (!$profile) {
            try {
                $profile = $this->automaticResolver->resolve(null)->name;
            } catch (\Exception $e) {
                $profile = null;
            }

            $profile = $this->style->choice('Profile', array_keys($profiles), $profile);
        }

        if (!$profile) {
            throw new \InvalidArgumentException('No (valid) profile provided.');
        }

        return $profiles[$profile];
    }
}
