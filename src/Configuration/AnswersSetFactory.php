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

use Rollerworks\Tools\SkeletonDancer\AnswersSet;
use Rollerworks\Tools\SkeletonDancer\ExpressionLanguage\AnswersProvider;
use Rollerworks\Tools\SkeletonDancer\ExpressionLanguage\VariablesProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

final class AnswersSetFactory
{
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function create(array $variables = [], array $defaults = []): AnswersSet
    {
        return new AnswersSet(
            function ($value, AnswersSet $answersSet) use ($variables) {
                static $variablesProvider, $answersProvider;

                if (null === $variablesProvider) {
                    $answersProvider = new AnswersProvider($answersSet);
                    $variablesProvider = new VariablesProvider([$this, 'resolveValue'], $variables, $answersProvider);
                }

                return $this->resolveValue($value, $variablesProvider, $answersProvider);
            }, $defaults
        );
    }

    /**
     * @internal
     *
     * @param mixed $value
     *
     * @return string|float|int|bool|array
     */
    public function resolveValue($value, VariablesProvider $variables, AnswersProvider $answers)
    {
        // Don't process array's as there only logical use-case is a multi-select choice.
        // And ignore string that don't begin with an `@`-sign.
        if (!is_string($value) || '' === $value || mb_strlen($value) < 3 || '@' !== $value[0]) {
            return $value;
        }

        $value = mb_substr($value, 1);

        // Value is escaped, so don't process.
        if ('@' === $value[0]) {
            return $value;
        }

        try {
            return $this->expressionLanguage->evaluate(
                $value,
                ['variables' => $variables, 'answers' => $answers]
            );
        } catch (SyntaxError $e) {
            throw new \InvalidArgumentException(
                sprintf('Syntax error in expression `%s`. Error: %s', $value, $e->getMessage()), 0, $e
            );
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Error with expression `%s`. Error: %s', $value, $e->getMessage()), 0, $e
            );
        }
    }
}
