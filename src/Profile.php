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
 * A Profile DataStructure holds the unresolved (and unmerged) information of a profile.
 */
final class Profile
{
    public $name;
    public $description = '';
    public $generators = [];
    public $configurators = [];
    public $variables = [];
    public $defaults = [];

    public function __construct(
        string $name,
        array $generators = [],
        array $configurators = [],
        array $variables = [],
        array $defaults = []
    ) {
        $this->name = $name;
        $this->generators = $generators;
        $this->configurators = $configurators;
        $this->variables = $variables;
        $this->defaults = $defaults;
    }
}
