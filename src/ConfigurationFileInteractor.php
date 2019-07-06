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

final class ConfigurationFileInteractor implements QuestionInteractor
{
    private $classInitializer;
    private $answers;

    public function __construct(ClassInitializer $classInitializer, string $configFile)
    {
        $this->classInitializer = $classInitializer;
        $this->answers = json_decode(file_get_contents($configFile), true);
    }

    public function interact(Dance $dance, bool $skipOptional = true): QuestionsSet
    {
        $questionCommunicator = function ($question, $name) {
            if (!\array_key_exists($name, $this->answers)) {
                throw new \InvalidArgumentException(
                    sprintf('Missing answer for "%s", did you provided the correct dance for the answers file?', $name)
                );
            }

            return $this->answers[$name];
        };

        $questions = new QuestionsSet($questionCommunicator, false);

        foreach ($dance->questioners as $configurator) {
            $this->classInitializer->getNewInstance($configurator, Questioner::class)->interact($questions);
        }

        return $questions;
    }
}
