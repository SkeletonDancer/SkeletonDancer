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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class DefaultsProcessor
{
    private $expressionLanguage;
    private $config;

    public function __construct(ExpressionLanguage $expressionLanguage, Config $config)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->config = $config;
    }

    public function process($profileName)
    {
        /** @var array $profileConfig */
        $profileConfig = $this->config->get(['profiles', $profileName]);
        /** @var array $defaults */
        $defaults = $this->config->get('defaults', []);

        if (!$profileConfig) {
            throw new \InvalidArgumentException(
                sprintf('Unable to process defaults of unregistered profile "%s".', $profileName)
            );
        }

        $defaults = array_merge($defaults, $profileConfig['defaults']);
        $resolvedDefaults = [];

        foreach ($defaults as $key => $value) {
            $resolvedDefaults[$key] = $this->resolveValue($value, $resolvedDefaults);
        }

        return $resolvedDefaults;
    }

    private function resolveValue($value, array $values)
    {
        // Don't process array's as there only logical use-case is a multi-select choice.
        // And ignore string that don't begin with an `@`-sign.
        if (!is_string($value) || '@' !== $value[0]) {
            return $value;
        }

        $value = mb_substr($value, 1);

        // Value is escaped, so don't process.
        if ('@' === $value[0]) {
            return $value;
        }

        try {
            return $this->expressionLanguage->evaluate($value, $values);
        } catch (SyntaxError $e) {
            throw new \InvalidArgumentException(
                sprintf('Syntax error in expression `%s`. Error: %s', $value, $e->getMessage()), 0, $e
            );
        }
    }
}
