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

use Rollerworks\Tools\SkeletonDancer\Exception\VariableCircularReferenceException;

/**
 * @internal
 */
final class VariablesProvider implements \ArrayAccess
{
    /**
     * Keep track of which variables are current being traversed.
     *
     * @var array
     */
    private $loading = [];

    /**
     * @var callable
     */
    private $valueResolver;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var AnswersProvider
     */
    private $answers;

    public function __construct(callable $valueResolver, array $variables, AnswersProvider $answers)
    {
        $this->valueResolver = $valueResolver;
        $this->variables = $variables;
        $this->answers = $answers;
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->variables);
    }

    public function offsetGet($key)
    {
        if (!array_key_exists($key, $this->variables)) {
            throw new \InvalidArgumentException(sprintf('Unable to get undefined variable "%s"', $key));
        }

        return $this->get($key);
    }

    public function offsetSet($offset, $value)
    {
        // no-op
    }

    public function offsetUnset($offset)
    {
        // no-op
    }

    public function get(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->variables)) {
            return $default;
        }

        if (isset($this->loading[$key])) {
            throw new VariableCircularReferenceException($key, array_keys($this->loading));
        }

        $this->loading[$key] = true;

        try {
            return ($this->valueResolver)($this->variables[$key], $this, $this->answers);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            unset($this->loading[$key]);
        }
    }
}
