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
     * @var array
     */
    private $answers = [];

    public function __construct(\Closure $communicator, $skipOptional = true)
    {
        $this->communicator = $communicator;
        $this->skipOptional = $skipOptional;
    }

    public function communicate(string $name = null, Question $question)
    {
        if (null !== $name && array_key_exists($name, $this->answers)) {
            throw new \InvalidArgumentException(sprintf('Answer "%s" already exists in the QuestionsSet.', $name));
        }

        $default = $this->resolveDefault($question->getDefault());

        if ($this->skipOptional && $question->isOptional()) {
            $value = $default;
        } else {
            $value = ($this->communicator)($question->createQuestion($default), $name);
        }

        if (null !== $name) {
            $answer = $value;
            $value = $question->getNormalizer() ? call_user_func($question->getNormalizer(), $value) : $value;

            $this->answers[$name] = $answer;
        }

        return $value;
    }

    public function set(string $name, $value)
    {
        if (array_key_exists($name, $this->answers)) {
            throw new \InvalidArgumentException(sprintf('Answer "%s" already exists in the QuestionsSet.', $name));
        }

        return $this->answers[$name] = $value;
    }

    public function get(string $name, $default = null)
    {
        return array_key_exists($name, $this->answers) ? $this->answers[$name] : $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->answers);
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    private function resolveDefault($default = null)
    {
        if ($default instanceof \Closure) {
            return $default($this->answers);
        }

        return $default;
    }
}
