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

namespace Rollerworks\Tools\SkeletonDancer\ExpressionLanguage;

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class Factory
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function create()
    {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->registerProvider(new StringProvider());
        $expressionLanguage->registerProvider(new FilesystemProvider());
        $expressionLanguage->register(
            'get_config',
            function () {
                return ''; // Not supported
            },
            function (array $arguments, $name, $default = null) {
                return $this->config->get($name, $default);
            }
        );
        $expressionLanguage->register(
            'date',
            function () {
                return ''; // Not supported
            },
            function (array $arguments, $format) {
                return date($format);
            }
        );

        return $expressionLanguage;
    }
}
