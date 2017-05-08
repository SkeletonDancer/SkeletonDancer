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
     * @var \Closure|null
     */
    private $validator;

    /**
     * @var \Closure|null
     */
    private $normalizer;

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
    private function __construct(\Closure $question, string $label, $default = null)
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
    public static function ask(string $label, $default = null, $validator = true): Question
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
                $validator = function ($v) {
                    return $v;
                };
            }
        }

        $question = function ($default, $help) use ($label, $validator) {
            return (new WrappedQuestion($label.$help, $default))->setValidator($validator);
        };

        $object = new self($question, $label, $default);
        $object->validator = $validator;

        return $object;
    }

    /**
     * @param string               $label
     * @param array                $choices
     * @param string|\Closure|null $default
     *
     * @return Question
     */
    public static function choice(string $label, array $choices, $default = null): Question
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
    public static function multiChoice(string $label, array $choices, array $default = null): Question
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
    public static function confirm(string $label, $default = true, string $trueAnswerRegex = '/^y/i'): Question
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
    public function createQuestion($default): WrappedQuestion
    {
        $question = $this->question;
        $help = $this->help ? ' ('.$this->help.')' : '';

        /** @var WrappedQuestion $actualQuestion */
        $actualQuestion = $question($default, $help);
        $actualQuestion->setAutocompleterValues($this->autosuggestionValues);
        $actualQuestion->setMaxAttempts($this->maxAttempts);

        return $actualQuestion;
    }

    public function isOptional(): bool
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
     * @param string|string[] $nullValues Value(s) that mark the value as null (empty)
     *
     * @return Question
     */
    public function markOptional($nullValues = null): Question
    {
        if (null === $this->default && null === $nullValues) {
            throw new \InvalidArgumentException(
                'Default value is NULL but but no $nullValues are provided. '.
                'Provide a value that will be transformed to NULL or set a default.'
            );
        }

        $this->optional = true;

        if (null !== $nullValues) {
            $nullValues = (array) $nullValues;

            $this->normalizer = function ($value) use ($nullValues) {
                if (in_array($value, $nullValues, true)) {
                    return;
                }

                return $value;
            };
        }

        return $this;
    }

    public function setHelp(string $text): Question
    {
        $this->help = $text;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setAutosuggestionValues(?iterable $autosuggestionValues): Question
    {
        $this->autosuggestionValues = $autosuggestionValues;

        return $this;
    }

    public function setMaxAttempts(int $maxAttempts = null): Question
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function getMaxAttempts(): ?int
    {
        return $this->maxAttempts;
    }

    public function getNormalizer(): ?\Closure
    {
        return $this->normalizer;
    }

    public function getValidator(): ?\Closure
    {
        return $this->validator;
    }
}
