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

namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Rollerworks\Tools\SkeletonDancer\AnswersSet;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\ProfileConfigResolver;
use Rollerworks\Tools\SkeletonDancer\Configurator\SkeletonDancerInitConfigurator;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Rollerworks\Tools\SkeletonDancer\Service\Filesystem;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Webmozart\Console\Api\IO\IO;

final class SkeletonDancerInitGenerator implements Generator
{
    private $config;
    private $filesystem;
    private $twig;
    private $io;

    /**
     * @var ProfileConfigResolver
     */
    private $profileConfigResolver;

    public function __construct(
        Config $config,
        ProfileConfigResolver $profileConfigResolver,
        Filesystem $filesystem,
        \Twig_Environment $twig,
        IO $consoleIo
    ) {
        $this->config = $config;
        $this->profileConfigResolver = $profileConfigResolver;
        $this->filesystem = $filesystem;
        $this->twig = $twig;
        $this->io = $consoleIo;
    }

    public function generate(array $configuration)
    {
        if (!$this->io->isInteractive()) {
            throw new \RuntimeException('This generator can only be run in interactive mode.');
        }

        $this->filesystem->mkdir('.dancer');

        $sharedVariables = [];
        $sharedDefaults = [];
        $profilesDefaults = [];

        if ($configuration['profiles']) {
            foreach ($configuration['profiles'] as $name) {
                $profilesDefaults[$name] = $this->getProfileResolvedConfig($name);
            }

            list($sharedVariables, $sharedDefaults, $profilesDefaults) = $this->resolveSharedCommons(
                $profilesDefaults
            );
        }

        $this->filesystem->dumpFile(
            '.dancer.yml',
            $this->twig->render(
                'dancer.yml.twig',
                [
                    'shared_variables' => $sharedVariables,
                    'shared_defaults' => $sharedDefaults,
                    'profiles_defaults' => $profilesDefaults,
                ]
            )
        );
    }

    public function getConfigurators()
    {
        return [SkeletonDancerInitConfigurator::class];
    }

    private function resolveSharedCommons(array $profiles)
    {
        $sharedVariables = [];
        $sharedDefaults = [];

        foreach ($profiles as $name => $profile) {
            foreach ($profiles as $name2 => $profile2) {
                if ($name === $name2) {
                    continue;
                }

                // Search for keys that exist in both the first and second defaults list.
                // Add them to the shared list and remove from both lists.

                foreach (array_intersect_key($profile['defaults'], $profile2['defaults']) as $keyName => $v) {
                    if ($profile['defaults'][$keyName] !== $profile2['defaults'][$keyName]) {
                        continue;
                    }

                    $sharedDefaults[$keyName] = $profile['defaults'][$keyName];

                    unset($profiles[$name]['defaults'][$keyName], $profiles[$name2]['defaults'][$keyName]);
                }

                foreach (array_intersect_key($profile['variables'], $profile2['variables']) as $keyName => $v) {
                    if ($profile['variables'][$keyName] !== $profile2['variables'][$keyName]) {
                        continue;
                    }

                    $sharedDefaults[$keyName] = $profile['variables'][$keyName];

                    unset($profiles[$name]['variables'][$keyName], $profiles[$name2]['variables'][$keyName]);
                }
            }
        }

        return [$sharedVariables, $sharedDefaults, $profiles];
    }

    private function getProfileResolvedConfig(string $profile)
    {
        $profileConfig = $this->profileConfigResolver->resolve($profile);

        $questionCommunicator = function (Question $question) {
            if ($question instanceof ChoiceQuestion && null !== $question->getDefault()) {
                $choices = $question->getChoices();
                $default = explode(',', (string) $question->getDefault());

                foreach ($default as &$defaultVal) {
                    $defaultVal = $choices[$defaultVal];
                }

                return implode(', ', $default);
            }

            return $question->getDefault() ?? '';
        };

        $answersSet = new AnswersSet(
            function ($value) {
                return null === $value ? '' : $value;
            }, $profileConfig->defaults
        );

        $questions = new QuestionsSet($questionCommunicator, $answersSet, false);

        foreach ($profileConfig->configurators as $configurator) {
            $configurator->interact($questions);
        }

        return ['variables' => $profileConfig->variables, 'defaults' => $questions->getAnswers()];
    }
}
