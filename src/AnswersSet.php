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

namespace Rollerworks\Tools\SkeletonDancer;

/**
 * Holds a set of given answers.
 *
 * The class serves to purposes, keeping a list of given answers
 * and resolving defaults for other answers (using the previously provided answers).
 *
 * The flow is as follow:
 *
 * * Call resolve('question-name', $default) // Default is what was provided by the Question
 *   Note a closure is will receive the provided as answers as first argument.
 *
 * * After an answer is provided (by the user), call set('question-name', 'value')
 *   Which will set the answer for other expressions and closure-defaults.
 *
 * * Continue with the other questions, repeating the steps above.
 *
 * Note: Calling set() is only possible once per answer.
 */
final class AnswersSet
{
    /**
     * @var mixed[]
     */
    private $answers = [];

    /**
     * @var mixed[]
     */
    private $values = [];

    /**
     * @var callable
     */
    private $defaultValueResolver;

    /**
     * Constructor.
     *
     * NB. Values starting a `@` are resolved as expressions,
     * use `@@` at the beginning to make value a literal (starting with `@`).
     *
     * @param callable $defaultValueResolver A callback used to resolve a default value,
     *                                       the callback receives (default-value, $this)
     * @param array[]  $defaults             Default values from all imported
     *                                       and inherited profiles
     */
    public function __construct(callable $defaultValueResolver, array $defaults)
    {
        $this->defaultValueResolver = $defaultValueResolver;
        $this->defaults = $defaults;
    }

    /**
     * @param string $key
     * @param mixed  $answer Answer provided by the user
     * @param mixed  $value  Normalized value
     *
     * @return mixed
     */
    public function set(string $key, $answer, $value)
    {
        if (array_key_exists($key, $this->answers)) {
            throw new \InvalidArgumentException(sprintf('An answer was already set for "%s"', $key));
        }

        $this->answers[$key] = $answer;
        $this->values[$key] = $value;

        return $value;
    }

    public function get(string $name, $default = null)
    {
        return array_key_exists($name, $this->answers) ? $this->answers[$name] : $default;
    }

    /**
     * Returns whether an answer was given for the key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->answers);
    }

    /**
     * Resolve a (default expression/closure) answer.
     *
     * @param string         $key
     * @param mixed|\Closure $default
     *
     * @return mixed|null
     */
    public function resolve(string $key = null, $default = null)
    {
        if (array_key_exists($key, $this->answers)) {
            return $this->answers[$key];
        }

        if (null === $key || !array_key_exists($key, $this->defaults)) {
            if ($default instanceof \Closure) {
                return $default($this->values, $this->answers);
            }

            return $default;
        }

        return call_user_func($this->defaultValueResolver, $this->defaults[$key], $this);
    }

    /**
     * Returns all the provided answers.
     */
    public function answers(): array
    {
        return $this->answers;
    }

    /**
     * Returns all the provided values.
     */
    public function values(): array
    {
        return $this->values;
    }
}
