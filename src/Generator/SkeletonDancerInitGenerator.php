<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configurator\Loader as ConfiguratorsLoader;
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
    private $configuratorsLoader;
    private $filesystem;
    private $twig;
    private $io;

    public function __construct(
        Config $config,
        ConfiguratorsLoader $configuratorLoader,
        Filesystem $filesystem,
        \Twig_Environment $twig,
        IO $consoleIo
    ) {
        $this->config = $config;
        $this->configuratorsLoader = $configuratorLoader;
        $this->filesystem = $filesystem;
        $this->twig = $twig;
        $this->io = $consoleIo;
    }

    public function generate(array $configuration)
    {
        if (!$this->io->isInteractive() || !$this->config->get('interactive', true)) {
            throw new \RuntimeException('This generator can only be run in interactive mode.');
        }

        $this->filesystem->mkdir('.dancer');

        $sharedDefaults = [];
        $profilesDefaults = [];

        if ($configuration['profiles']) {
            foreach ($configuration['profiles'] as $name) {
                $profilesDefaults[$name]['defaults'] = $this->getProfileQuestionsAndDefaults($name);
            }

            list($sharedDefaults, $profilesDefaults) = $this->resolveSharedDefaults($profilesDefaults);
        }

        $this->filesystem->dumpFile(
            '.dancer.yml',
            $this->twig->render(
                'dancer.yml.twig',
                [
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

    private function resolveSharedDefaults(array $profiles)
    {
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
            }
        }

        return [$sharedDefaults, $profiles];
    }

    private function getProfileQuestionsAndDefaults($profile)
    {
        /** @var array $profileConfig */
        $profileConfig = $this->config->get(['profiles', $profile]);
        $generatorClasses = $profileConfig['generators'];
        $defaults = $profileConfig['defaults'];

        $this->configuratorsLoader->clear();

        foreach ($generatorClasses as $generatorClass) {
            $this->configuratorsLoader->loadFromGenerator($generatorClass);
        }

        $questionCommunicator = function (Question $question) {
            if ($question instanceof ChoiceQuestion && null !== $question->getDefault()) {
                $choices = $question->getChoices();
                $default = explode(',', $question->getDefault());

                foreach ($default as &$defaultVal) {
                    $defaultVal = $choices[$defaultVal];
                }

                return implode(', ', $default);
            }

            return $question->getDefault();
        };

        $questions = new QuestionsSet($questionCommunicator, $defaults, false);
        $configurators = $this->configuratorsLoader->getConfigurators();

        foreach ($configurators as $configurator) {
            $configurator->interact($questions);
        }

        return $questions->getAnswers();
    }
}
