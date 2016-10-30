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

namespace Rollerworks\Tools\SkeletonDancer\Exception;

final class VariableCircularReferenceException extends \InvalidArgumentException
{
    private $key;
    private $path;

    public function __construct(string $key, array $path, \Exception $previous = null)
    {
        parent::__construct(
            sprintf('Circular reference detected for variable "%s", path: "%s".', $key, implode(' -> ', $path)),
            0,
            $previous
        );

        $this->key = $key;
        $this->path = $path;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getPath()
    {
        return $this->path;
    }
}
