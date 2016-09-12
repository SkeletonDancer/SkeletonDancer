<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer;

final class StringUtil
{
    /**
     * A string to underscore.
     *
     * @param string $id The string to underscore
     *
     * @return string The underscored string
     */
    public static function underscore($id)
    {
        return strtolower(
            preg_replace(
                ['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'],
                ['\\1_\\2', '\\1_\\2'],
                str_replace('.', '_', $id)
            )
        );
    }

    /**
     * Camelizes a string.
     *
     * @param string $id A string to camelize
     *
     * @return string The camelized string
     */
    public static function camelize($id)
    {
        return strtr(ucwords(strtr($id, ['_' => ' ', '.' => '_ ', '\\' => '_ '])), [' ' => '']);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function humanize($text)
    {
        return trim(ucfirst(trim(strtolower(preg_replace(['/((?<![-._])[A-Z])/', '/[\s]+/'], ['-$1', '-'], $text)))), '-');
    }

    public static function shortProductName($name)
    {
        return preg_replace('#[^\w\d_-]|\s#', '', ucfirst($name));
    }

    public static function vendorNamespace($name)
    {
        return strtr(ucwords(strtr($name, ['_' => ' ', '.' => '_ ', '\\' => '_ ', '-' => ' '])), [' ' => '']);
    }

    /**
     * Get Nth directory name of the path.
     *
     * Path `src/Generators/` with index 1 will return `Generators`.
     *
     * @param string $path
     * @param int    $index   Zero index position
     * @param string $default Default value to return when the index doesn't exist
     *
     * @return string
     */
    public static function getNthDirname($path, $index, $default = '')
    {
        $dirs = explode('/', rtrim(str_replace('\\', '/', $path), '/'));

        if (isset($dirs[$index])) {
            return $dirs[$index];
        }

        return $default;
    }
}
