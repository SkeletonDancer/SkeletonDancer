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

/**
 * A Dance specifies what needs to be done, which Questioners must
 * be executed and which generators must be run afterwards.
 */
final class Dance
{
    public $name;
    public $title;
    public $description;
    public $directory;
    public $questioners = [];
    public $generators = [];

    public $autoloading = [];

    public function __construct(string $name, string $directory, array $questioners = [], array $generators = [])
    {
        $this->name = $name;
        $this->directory = $directory;
        $this->generators = $generators;
        $this->questioners = $questioners;
    }
}
