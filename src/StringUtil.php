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

namespace SkeletonDancer;

final class StringUtil
{
    /**
     * Split lines to an array.
     *
     * @param string $input
     *
     * @return string[]
     */
    public static function splitLines(string $input): array
    {
        $input = trim($input);

        return ('' === $input) ? [] : preg_split('{\r?\n}', $input);
    }

    /**
     * Converts to string to underscore.
     *
     * @param string $string
     *
     * @return string The under_scored string
     */
    public static function underscore(string $string): string
    {
        return mb_strtolower(
            preg_replace(
                ['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'],
                ['\\1_\\2', '\\1_\\2'],
                str_replace('.', '_', $string)
            )
        );
    }

    /**
     * Camelizes a string.
     *
     * @param string $string
     *
     * @return string The string in CamelCase
     */
    public static function camelize(string $string): string
    {
        return strtr(ucwords(strtr($string, ['_' => ' ', '.' => '_ ', '\\' => '_ '])), [' ' => '']);
    }

    /**
     * Convert a string to camelCase (first character is lowercase).
     *
     * @param string $string
     *
     * @return string The string in snakeCase
     */
    public static function camelHumps(string $string): string
    {
        return lcfirst(self::camelize($string));
    }

    /**
     * Humanize a string.
     *
     * @param string $text
     *
     * @return string
     */
    public static function humanize(string $text): string
    {
        return ucfirst(mb_strtolower(trim(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
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
    public static function getNthDirname(string $path, int $index, string $default = ''): string
    {
        $dirs = explode('/', rtrim(str_replace('\\', '/', $path), '/'));

        if ($index < 0) {
            $index = \count($dirs) - abs($index);
        }

        return $dirs[$index] ?? $default;
    }

    /**
     * Comment all lines with the character.
     *
     * @param string $text
     * @param string $comment Comment character(s)
     *
     * @return string
     */
    public static function commentLines(string $text, string $comment = '#'): string
    {
        if ('' === $text) {
            return '';
        }

        return $comment.preg_replace("/(\n|\r\n)/", "\\1$comment", $text);
    }

    /**
     * Indent all lines with n-level.
     *
     * @param string $text
     * @param int    $level  Level of the indention (starting with 1)
     * @param string $indent The indentation (eg. 4 space characters)
     *
     * @return string
     */
    public static function indentLines(string $text, int $level = 1, string $indent = '    '): string
    {
        if ('' === $text) {
            return '';
        }

        $indentation = str_repeat($indent, $level);

        return $indentation.preg_replace("/(\n|\r\n)/", '\\1'.$indentation, $text);
    }
}
