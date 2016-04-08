<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\ExpressionLanguage;

use Rollerworks\Tools\SkeletonDancer\StringUtil;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class StringProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'substr',
                function ($str, $start, $length = null) {
                    return sprintf('substr(%s, %d, %d)', $str, $start, $length);
                },
                function (array $values, $str, $start, $length = null) {
                    return null === $length ? substr($str, $start, $length) : substr($str, $start);
                }
            ),
            new ExpressionFunction(
                'strpos',
                function ($haystack, $needle, $offset = 0) {
                    return sprintf('strpos(%s, %s, %d)', $haystack, $needle, $offset);
                },
                function (array $values, $haystack, $needle, $offset = 0) {
                    return strpos($haystack, $needle, $offset);
                }
            ),
            new ExpressionFunction(
                'strrpos',
                function ($haystack, $needle, $offset = 0) {
                    return sprintf('strrpos(%s, %s, %d)', $haystack, $needle, $offset);
                },
                function (array $values, $haystack, $needle, $offset = 0) {
                    return strrpos($haystack, $needle, $offset);
                }
            ),
            new ExpressionFunction(
                'ucfirst',
                function ($str) {
                    return sprintf('ucfirst(%s)', $str);
                },
                function (array $values, $str) {
                    return ucfirst($str);
                }
            ),
            new ExpressionFunction(
                'lowercase',
                function ($str) {
                    return sprintf('strtolower(%s)', $str);
                },
                function (array $values, $str) {
                    return strtolower($str);
                }
            ),
            new ExpressionFunction(
                'uppercase',
                function ($str) {
                    return sprintf('strtoupper(%s)', $str);
                },
                function (array $values, $str) {
                    return strtoupper($str);
                }
            ),
            new ExpressionFunction(
                'replace',
                function ($search, $replace, $subject) {
                    return sprintf('str_replace(%s, %s, %s)', $search, $replace, $subject);
                },
                function (array $values, $search, $replace, $subject) {
                    return str_replace($search, $replace, $subject);
                }
            ),
            new ExpressionFunction(
                'preg_replace',
                function ($search, $replace, $subject) {
                    return sprintf('preg_replace(%s, %s, %s)', $search, $replace, $subject);
                },
                function (array $values, $search, $replace, $subject) {
                    return preg_replace($search, $replace, $subject);
                }
            ),

            new ExpressionFunction(
                'underscore',
                function ($input) {
                    return sprintf('\Rollerworks\Tools\SkeletonDancer\StringUtil::underscore(%s)', $input);
                },
                function (array $values, $input) {
                    return StringUtil::underscore($input);
                }
            ),
            new ExpressionFunction(
                'camelize',
                function ($input) {
                    return sprintf('\Rollerworks\Tools\SkeletonDancer\StringUtil::camelize(%s)', $input);
                },
                function (array $values, $input) {
                    return StringUtil::camelize($input);
                }
            ),
            new ExpressionFunction(
                'humanize',
                function ($input) {
                    return sprintf('\Rollerworks\Tools\SkeletonDancer\StringUtil::humanize(%s)', $input);
                },
                function (array $values, $input) {
                    return StringUtil::humanize($input);
                }
            ),
        ];
    }
}
