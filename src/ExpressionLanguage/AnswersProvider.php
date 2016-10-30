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

use Rollerworks\Tools\SkeletonDancer\AnswersSet;

/**
 * @internal
 */
final class AnswersProvider implements \ArrayAccess
{
    /**
     * @var AnswersSet
     */
    private $answersSet;

    public function __construct(AnswersSet $answersSet)
    {
        $this->answersSet = $answersSet;
    }

    public function offsetExists($key)
    {
        return $this->answersSet->has($key);
    }

    public function offsetGet($key)
    {
        if (!$this->answersSet->has($key)) {
            throw new \InvalidArgumentException(sprintf('Unable to get value for unresolved answer "%s"', $key));
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
        return $this->answersSet->get($key, $default);
    }
}
