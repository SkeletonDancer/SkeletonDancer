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

final class ProfilesProcessor
{
    private $profiles = [];
    private $loading = [];

    public function process(array $profiles)
    {
        $this->profiles = $profiles;

        foreach ($profiles as $name => $profile) {
            $this->loading = [$name];
            $this->profiles[$name] = $this->processProfile($name, $profile);
            $this->profiles[$name]['description'] = $profile['description'];
            $this->profiles[$name]['generators'] = array_unique($this->profiles[$name]['generators']);
        }

        return $this->profiles;
    }

    private function processProfile($name, array $profile)
    {
        if (!empty($profile['import'])) {
            $resolvedProfile = $this->processImports($name, $profile['import']);
        } else {
            $resolvedProfile = ['generators' => [], 'defaults' => [], 'import' => []];
        }

        if (isset($profile['generators'])) {
            $resolvedProfile['generators'] = array_merge($resolvedProfile['generators'], $profile['generators']);
        }

        if (isset($profile['defaults'])) {
            $resolvedProfile['defaults'] = array_merge($resolvedProfile['defaults'], $profile['defaults']);
        }

        return $resolvedProfile;
    }

    private function processImports($name, array $imports)
    {
        $profile = ['generators' => [], 'defaults' => [], 'import' => $imports];

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

            $processProfile = $this->processProfile($import, $this->profiles[$import]);

            // Do merging per type to ensure defaults are merged (not overwritten)
            // array_merge_recursive() has no depth limit and causes problems with multi-choice answers.
            $profile['generators'] = array_merge($profile['generators'], $processProfile['generators']);
            $profile['defaults'] = array_merge($profile['defaults'], $processProfile['defaults']);
        }

        return $profile;
    }
}
