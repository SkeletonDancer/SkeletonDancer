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

final class Dances implements \Countable, \IteratorAggregate
{
    /** @var Dance[] */
    private $dances = [];

    /**
     * @param Dance[] $dances
     */
    public function __construct(array $dances = [])
    {
        foreach ($dances as $dance) {
            $this->dances[$dance->name] = $dance;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->dances[$name]);
    }

    public function get(string $name): Dance
    {
        if (isset($this->dances[$name])) {
            return $this->dances[$name];
        }

        throw new \InvalidArgumentException(sprintf('Dance "%s" was not found or installed.', $name));
    }

    /**
     * @return Dances[]
     */
    public function all(): array
    {
        return $this->dances;
    }

    /**
     * @return string[]
     */
    public function names(): array
    {
        return array_keys($this->dances);
    }

    public function count()
    {
        return \count($this->dances);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->dances);
    }
}
