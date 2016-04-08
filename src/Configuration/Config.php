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

final class Config
{
    /**
     * Configuration tree.
     *
     * @var array
     */
    private $config = [];

    /**
     * List of protected configuration keys (root level only).
     *
     * @var array
     */
    private $protected = [];

    /**
     * Constructor.
     *
     * @param array $config
     * @param array $protected
     */
    public function __construct(array $config, array $protected = [])
    {
        $this->config = $config;
        $this->protected = $protected;
    }

    /**
     * Set a constant (unchangeable value).
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setConstant($name, $value)
    {
        $this->set($name, $value);

        $this->protected[] = $name;
    }

    /**
     * Removes a configuration at the root level.
     *
     * @param string|string[] $keys Single level key like 'profiles' or array-path
     *                              like ['profiles', 'symfony-bundle']
     */
    public function remove($keys)
    {
        $keys = (array) $keys;

        if (in_array($keys[0], $this->protected, true)) {
            throw new \InvalidArgumentException(
                sprintf('Configuration key "%s" is protected and cannot be removed.', $keys)
            );
        }

        if (!array_key_exists($keys[0], $this->config)) {
            return;
        }

        $current = &$this->config;
        $t = count($keys);
        $i = 1;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $current)) {
                break;
            }

            // Stop at the end and remove the value at key position.
            // Do this in the loop to prevent analyzer warnings.
            if ($t === $i) {
                unset($current[$key]);
            } else {
                $current = &$current[$key];
            }

            ++$i;
        }
    }

    /**
     * Merges new config values with the existing ones (overriding).
     *
     * This can only store a single-key level like "adapters" but
     * not ['profiles', 'symfony-bundle'].
     *
     * @param string                      $key   Single level config key
     * @param string|int|float|bool|array $value Value to store
     *
     * @return Config
     */
    public function set($key, $value)
    {
        if (is_array($key)) {
            throw new \InvalidArgumentException(
                'Invalid configuration, cannot set nested configuration-key". '.
                'Store the top config instead like: key => [sub_key => value].'
            );
        }

        if (!is_scalar($value) && !is_array($value)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Configuration can only a be scalar or an array, "%s" type given instead for "%s".',
                    gettype($value),
                    $key
                )
            );
        }

        if (in_array($key, $this->protected, true)) {
            throw new \InvalidArgumentException(
                sprintf('Configuration key "%s" is protected and cannot be overwritten.', $key)
            );
        }

        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Merges new config values with the existing ones (overriding).
     *
     * @param array $config
     */
    public function merge(array $config)
    {
        foreach ($config as $key => $val) {
            $this->set($key, $val);
        }
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
     *                                             like ['profiles', 'symfony-bundle'] to check.
     * @param string|int|float|bool|array $default Default value to use when no config is found (null).
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
