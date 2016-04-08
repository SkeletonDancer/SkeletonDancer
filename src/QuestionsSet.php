<?php

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
     * @var array
     */
    private $answers = [];

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
    private $defaults;

    public function __construct(\Closure $communicator, array $defaults = [], $skipOptional = true)
    {
        $this->communicator = $communicator;
        $this->defaults = $defaults;
        $this->skipOptional = $skipOptional;
    }

    public function communicate($name, Question $question)
    {
        if (null !== $name && array_key_exists($name, $this->answers)) {
            throw new \InvalidArgumentException(sprintf('Question with name "%s" already exists in the QuestionsSet.', $name));
        }

        $default = isset($this->defaults[$name]) ? $this->defaults[$name] : null;
        $default = $this->resolveDefault($question, $this->answers, $default);

        if ($this->skipOptional && $question->isOptional()) {
            $value = $default;
        } else {
            $communicator = $this->communicator;
            $value = $communicator($question->createQuestion($default), $name);
        }

        if (null !== $name) {
            $this->answers[$name] = $value;
        }

        return $value;
    }

    public function set($name, $value)
    {
        if (array_key_exists($name, $this->answers)) {
            throw new \InvalidArgumentException(sprintf('Question with name "%s" already exists in the QuestionsSet.', $name));
        }

        return $this->answers[$name] = $value;
    }

    public function get($name, $default = null)
    {
        return isset($this->answers[$name]) ? $this->answers[$name] : $default;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->answers);
    }

    public function all()
    {
        return $this->answers;
    }

    /**
     * @param Question $question
     * @param array    $configuration
     * @param null     $default
     *
     * @return null|string
     */
    private function resolveDefault(Question $question, array $configuration, $default = null)
    {
        if (null !== $default) {
            return $default;
        }

        $default = $question->getDefault();

        if ($default instanceof \Closure) {
            $default = $default($configuration);
        }

        return $default;
    }
}
