<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Configuration;

use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\IO\IO;

final class InteractiveProfileResolver implements ProfileResolver
{
    private $config;
    private $style;
    private $io;
    private $automaticResolver;

    public function __construct(
        Config $config,
        SymfonyStyle $style,
        IO $io,
        AutomaticProfileResolver $automaticResolver
    ) {
        $this->config = $config;
        $this->style = $style;
        $this->io = $io;
        $this->automaticResolver = $automaticResolver;
    }

    public function resolve($profile = null)
    {
        if (null !== $profile && !$this->config->has(['profiles', $profile])) {
            if ($this->io->isInteractive()) {
                $this->style->error(
                    sprintf(
                        'Profile "%s" is not registered, please use one of the following: %s.',
                        $profile,
                        implode(', ', array_keys($this->config->get(['profiles'])))
                    )
                );
            }

            $profile = null;
        }

        if (!$profile) {
            try {
                $profile = $this->automaticResolver->resolve(null);
            } catch (\Exception $e) {
                $profile = null;
            }

            $profile = $this->style->choice('Profile', array_keys($this->config->get('profiles')), $profile);
        }

        if (!$profile) {
            throw new \InvalidArgumentException('No (valid) profile provided.');
        }

        return $profile;
    }
}
