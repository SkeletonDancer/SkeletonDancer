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

final class ResolvedProfile
{
    public $name;
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

    /**
     * @param Questioner $questioner
     * @param Runner     $runner
     * @param array      $cachedAnswers Cached answers (from a previous run)
     */
    public function execute(Questioner $questioner, Runner $runner, array $cachedAnswers = [])
    {
        $answers = $questioner->interact(
            $this->configurators,
            $runner->skipOptional(),
            $this->variables,
            [] !== $cachedAnswers ? $cachedAnswers : $this->defaults
        );

        $runner->run($this, $this->generators, $answers);
    }
}
