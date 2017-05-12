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

namespace SkeletonDancer\Service;

use SkeletonDancer\Dance;
use SkeletonDancer\StringUtil;
use Symfony\Component\Yaml\Yaml;

class TwigTemplating
{
    public function create(Dance $dance): \Twig_Environment
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates');
        $loader->addPath($dance->directory.'/templates');

        $twig = new \Twig_Environment(
            $loader,
            [
                'debug' => true,
                'cache' => new \Twig_Cache_Filesystem(sys_get_temp_dir().'/twig'),
                'strict_variables' => true,
            ]
        );

        $twig->addFunction(
            new \Twig_SimpleFunction(
                'doc_header',
                function ($value, $format) {
                    return $value."\n".str_repeat($format, mb_strlen($value));
                }
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'normalizeNamespace',
                function ($value) {
                    return str_replace('\\\\', '\\', $value);
                },
                ['is_safe' => ['html', 'yml', 'yaml']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'camelize',
                [StringUtil::class, 'camelize']
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'camelize_method',
                [StringUtil::class, 'camelizeMethodName'],
                ['is_safe' => ['all']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'underscore',
                [StringUtil::class, 'underscore'],
                ['is_safe' => ['all']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'escape_namespace',
                'addslashes',
                ['is_safe' => ['html', 'yml', 'yaml']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'indent_lines',
                [StringUtil::class, 'indentLines'],
                ['is_safe' => ['all']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'comment_lines',
                [StringUtil::class, 'commentLines'],
                ['is_safe' => ['all']]
            )
        );

        $twig->addFilter(
            new \Twig_SimpleFilter(
                'yaml_dump',
                function ($value, $inline = 4, $indent = 4, $flags = 0) {
                    return Yaml::dump($value, $inline, 4, $indent, $flags);
                },
                ['is_safe' => ['yml', 'yaml']]
            )
        );

        return $twig;
    }
}
