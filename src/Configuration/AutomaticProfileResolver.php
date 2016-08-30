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

final class AutomaticProfileResolver implements ProfileResolver
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function resolve($profile = null)
    {
        if (null !== $profile && !$this->config->has(['profiles', $profile])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Profile "%s" is not registered, please use one of the following: "%s".',
                    $profile,
                    implode('", "', array_keys($this->config->get(['profiles'])))
                )
            );
        }

        if (null === $resolver = $this->config->get('profile_resolver')) {
            throw new \RuntimeException(
                'Unable to automatically resolve the correct profile, no profile resolver was configured. '.
                'Provide the profile manually.'
            );
        }

        $folder = $this->config->get('current_dir_relative');

        if (is_string($resolver)) {
            $profile = $this->resolveByCustomClass($resolver, $folder);
        } else {
            $profile = $this->resolveByPatternMap($resolver, $folder);
        }

        if (null === $profile) {
            throw new \RuntimeException(
                'Was unable to automatically resolve the correct profile, profile resolver did not return a result. '.
                'Please check your configuration/resolver or provide the profile manually.'
            );
        }

        if (!$this->config->has(['profiles', $profile])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Profile "%s" returned by the profile resolver is not registered, please check your configuration.',
                    $profile
                )
            );
        }

        return $profile;
    }

    private function resolveByCustomClass($class, $folder)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to automatically resolve the correct profile, invalid profile resolver configured. '.
                    'No such class: "%s".',
                    $class
                )
            );
        }

        if (!method_exists($class, 'resolve')) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to automatically resolve the correct profile, invalid profile resolver configured. '.
                    'Method resolve() does not exist in class "%s".',
                    $class
                )
            );
        }

        $resolver = new $class();

        return $resolver->resolve($folder, $this->config);
    }

    private function resolveByPatternMap(array $map, $folder)
    {
        foreach ($map as $pattern => $profile) {
            $regex = $this->toRegex($pattern);

            if (preg_match($regex, $folder)) {
                return $profile;
            }
        }
    }

    /**
     * Converts strings to regexp.
     *
     * PCRE patterns are left unchanged.
     *
     * Default conversion:
     *     'lorem/ipsum/dolor' ==>  'lorem\/ipsum\/dolor/'
     *
     * Use only / as directory separator (on Windows also).
     *
     * @param string $str Pattern: regexp or dirname
     *
     * @return string regexp corresponding to a given string or regexp
     */
    private function toRegex($str)
    {
        return $this->isRegex($str) ? $str : '/'.preg_quote($str, '/').'/';
    }

    /**
     * Checks whether the string is a regex.
     *
     * @param string $str
     *
     * @return bool Whether the given string is a regex
     *
     * @author Victor Berchet (Symfony Finder component)
     */
    private function isRegex($str)
    {
        if (!preg_match('/^(.{3,}?)[imsxuADU]*$/', $str, $m)) {
            return false;
        }

        $start = substr($m[1], 0, 1);
        $end = substr($m[1], -1);

        if ($start === $end) {
            return !preg_match('/[*?[:alnum:] \\\\]/', $start);
        }

        foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
            if ($start === $delimiters[0] && $end === $delimiters[1]) {
                return true;
            }
        }

        return false;
    }
}
