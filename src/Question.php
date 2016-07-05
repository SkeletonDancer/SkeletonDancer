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

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question as WrappedQuestion;

final class Question
{
    /**
     * @var null|string|\Closure
     */
    private $default;

    /**
     * @var \Closure
     */
    private $question;

    /**
     * @var array
     */
    private $autosuggestionValues;

    /**
     * @var bool
     */
    private $optional = false;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $help;

    /**
     * @var int|null
     */
    private $maxAttempts = null;

    /**
     * GenericQuestion constructor.
     *
     * @param \Closure             $question
     * @param string               $label
     * @param \Closure|string|null $default
     */
    private function __construct(\Closure $question, $label, $default = null)
    {
        $this->label = $label;
        $this->question = $question;
        $this->default = $default;
    }

    /**
     * @param string               $label
     * @param string|\Closure|null $default
     * @param \Closure|string|bool $validator
     *
     * @return Question
     */
    public static function ask($label, $default = null, $validator = true)
    {
        if (is_string($validator)) {
            $validator = function ($value) use ($validator) {
                if (!preg_match($validator, $value)) {
                    throw new \InvalidArgumentException(sprintf('Value does not match regex pattern: %s', $validator));
                }

                return $value;
            };
        } elseif (is_bool($validator)) {
            if (true === $validator) {
                $validator = function ($value) {
                    if (!is_array($value) && !is_bool($value) && '' === (string) $value) {
                        throw new \InvalidArgumentException('A value is required.');
                    }

                    return $value;
                };
            } else {
                // No-op validator to allow empty value.
                $validator = null;
            }
        }

        $question = function ($default, $help) use ($label, $validator) {
            return (new WrappedQuestion($label.$help, $default))->setValidator($validator);
        };

        return new self($question, $label, $default);
    }

    /**
     * @param string               $label
     * @param array                $choices
     * @param string|\Closure|null $default
     *
     * @return Question
     */
    public static function choice($label, array $choices, $default = null)
    {
        $question = function ($default, $help) use ($label, $choices) {
            if (null !== $default && !isset($choices[$default])) {
                $default = array_search($default, $choices, true);
            }

            return new ChoiceQuestion($label.$help, $choices, $default);
        };

        return new self($question, $label, $default);
    }

    /**
     * @param string                     $label
     * @param array                      $choices
     * @param string|array|\Closure|null $default
     *
     * @return Question
     */
    public static function multiChoice($label, array $choices, array $default = null)
    {
        $question = function ($defaults, $help) use ($label, $choices) {
            if (null !== $defaults) {
                $defaults = (array) $defaults;

                foreach ($defaults as $i => $default) {
                    if (!isset($choices[$default])) {
                        $defaults[$i] = array_search($default, $choices, true);
                    }
                }

                $defaults = implode(',', $defaults);
            }

            return (new ChoiceQuestion($label.$help, $choices, $defaults))->setMultiselect(true);
        };

        return new self($question, $label, $default);
    }

    /**
     * @param string        $label
     * @param bool|\Closure $default
     * @param string        $trueAnswerRegex
     *
     * @return Question
     */
    public static function confirm($label, $default = true, $trueAnswerRegex = '/^y/i')
    {
        $question = function ($default, $help) use ($label, $trueAnswerRegex) {
            return new ConfirmationQuestion($label.$help, $default, $trueAnswerRegex);
        };

        return new self($question, $label, $default);
    }

    /**
     * @param mixed $default
     *
     * @return WrappedQuestion
     */
    public function createQuestion($default)
    {
        $question = $this->question;
        $help = $this->help ? ' ('.$this->help.')' : '';

        /** @var WrappedQuestion $actualQuestion */
        $actualQuestion = $question($default, $help);
        $actualQuestion->setAutocompleterValues($this->autosuggestionValues);
        $actualQuestion->setMaxAttempts($this->maxAttempts);

        return $actualQuestion;
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * @return null|string|\Closure
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return $this
     */
    public function markOptional()
    {
        $this->optional = true;

        return $this;
    }

    public function setHelp($text)
    {
        $this->help = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * @param null|array|\Traversable $autosuggestionValues
     *
     * @return Question
     */
    public function setAutosuggestionValues($autosuggestionValues)
    {
        $this->autosuggestionValues = $autosuggestionValues;

        return $this;
    }

    /**
     * @param int|null $maxAttempts
     *
     * @return Question
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }
}
