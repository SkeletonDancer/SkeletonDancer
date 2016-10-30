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

final class QuestionsSet
{
    /**
     * @var \Closure
     */
    private $communicator;

    /**
     * @var bool
     */
    private $skipOptional;

    /**
     * @var AnswersSet
     */
    private $answersSet;

    public function __construct(\Closure $communicator, AnswersSet $answersSet, $skipOptional = true)
    {
        $this->communicator = $communicator;
        $this->answersSet = $answersSet;
        $this->skipOptional = $skipOptional;
    }

    public function communicate(string $name = null, Question $question)
    {
        $this->answersSet->has($name);

        if (null !== $name && $this->answersSet->has($name)) {
            throw new \InvalidArgumentException(sprintf('Question with name "%s" already exists in the QuestionsSet.', $name));
        }

        $default = $this->answersSet->resolve($name, $question->getDefault());

        if ($this->skipOptional && $question->isOptional()) {
            $value = $default;
        } else {
            $value = ($this->communicator)($question->createQuestion($default), $name);
        }

        if (null !== $name) {
            $answer = $value;
            $value = $question->getNormalizer() ? call_user_func($question->getNormalizer(), $value) : $value;

            $this->answersSet->set($name, $answer, $value);
        }

        return $value;
    }

    public function set(string $name, $value)
    {
        return $this->answersSet->set($name, $value, $value);
    }

    public function get(string $name, $default = null)
    {
        return $this->answersSet->get($name, $default);
    }

    public function has(string $name): bool
    {
        return $this->answersSet->has($name);
    }

    public function getAnswers(): array
    {
        return $this->answersSet->answers();
    }

    /**
     * Returns the finalized values.
     *
     * @param Configurator[] $configurators
     *
     * @return array
     */
    public function getFinalizedValues(array $configurators): array
    {
        $values = $this->getValues();

        foreach ($configurators as $finalizer) {
            $finalizer->finalizeConfiguration($values);
        }

        return $values;
    }

    public function getValues(): array
    {
        return $this->answersSet->values();
    }
}
