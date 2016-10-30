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

final class Config
{
    /**
     * Configuration tree.
     *
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $profiles;

    /**
     * Constructor.
     *
     * @param array     $configuration
     * @param Profile[] $profiles
     */
    public function __construct(array $configuration, array $profiles)
    {
        $this->config = $configuration;
        $this->profiles = $profiles;
    }

    /**
     * @return Profile[]
     */
    public function getProfiles(): array
    {
        return $this->profiles;
    }

    /**
     * Returns a config value.
     *
     * @param string|string[]             $keys    Single level key like 'profiles' or array-path
     *                                             like ['profiles', 'symfony-bundle']
     * @param string|int|float|bool|array $default Default value to use when no config is found (null)
     *
     * @return array|bool|float|int|string
     */
    public function get($keys, $default = null)
    {
        $keys = (array) $keys;

        if (count($keys) === 1) {
            return array_key_exists($keys[0], $this->config) ? $this->config[$keys[0]] : $default;
        }

        $current = $this->config;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return $default;
            }

            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Returns the first none-null configuration value.
     *
     * @param string[]                    $keys    Array of single level keys like "adapters" or array-path
     *                                             like ['profiles', 'symfony-bundle'] to check
     * @param string|int|float|bool|array $default Default value to use when no config is found (null)
     *
     * @return array|bool|float|int|string
     */
    public function getFirstNotNull(array $keys, $default = null)
    {
        foreach ($keys as $key) {
            $value = $this->get($key);

            if (null !== $value) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get the configuration is as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * Checks whether the config exists.
     *
     * @param string $keys Single level key like "profiles" or array-path
     *                     like ['profiles', 'symfony-bundle']
     *
     * @return bool
     */
    public function has($keys)
    {
        $keys = (array) $keys;

        if (count($keys) === 1) {
            return array_key_exists($keys[0], $this->config);
        }

        $current = $this->config;

        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return false;
            }

            $current = $current[$key];
        }

        return true;
    }
}
