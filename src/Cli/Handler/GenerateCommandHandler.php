<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Cli\Handler;

use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\DefaultsProcessor;
use Rollerworks\Tools\SkeletonDancer\Configuration\ProfileResolver;
use Rollerworks\Tools\SkeletonDancer\Configurator\Loader;
use Rollerworks\Tools\SkeletonDancer\Generator;
use Rollerworks\Tools\SkeletonDancer\Questioner\InteractiveQuestioner;
use Rollerworks\Tools\SkeletonDancer\Questioner\UsingDefaultsQuestioner;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;

final class GenerateCommandHandler
{
    private $style;
    private $configuratorsLoader;
    private $config;
    private $profileResolver;
    private $defaultsProcessor;

    public function __construct(
        SymfonyStyle $style,
        Loader $configuratorsLoader,
        Config $config,
        ProfileResolver $profileResolver,
        DefaultsProcessor $defaultsProcessor
    ) {
        $this->style = $style;
        $this->configuratorsLoader = $configuratorsLoader;
        $this->config = $config;
        $this->profileResolver = $profileResolver;
        $this->defaultsProcessor = $defaultsProcessor;
    }

    public function handle(Args $args, IO $io)
    {
        $this->style->title('SkeletonDancer - PHP Project bootstrapping');

        if ($io->isVerbose()) {
            $this->style->text(
                [
                    sprintf('// Using config file: %s', $this->config->get('config_file', '')),
                    sprintf('// Project directory: %s', $this->config->get('project_directory', '')),
                    sprintf('// Dancer config directory: %s', $this->config->get('dancer_directory', '')),
                    '',
                ]
            );
        }

        $profile = $this->profileResolver->resolve($args->getArgument('profile'));
        $this->config->setConstant('active_profile', $profile);

        $this->style->text(sprintf('Using profile: %s', $profile));

        $generators = $this->initGenerators($this->config->get(['profiles', $profile, 'generators']));
        $this->loadAdditionalConfigurators($this->config->get(['profiles', $profile, 'configurators'], []));

        $configuration = $this->getConfiguration($args, $profile);

        if (!$this->executeGenerators($generators, $configuration)) {
            return 1;
        }
    }

    /**
     * @param string[] $generatorClasses
     *
     * @return Generator[]
     */
    private function initGenerators(array $generatorClasses)
    {
        /** @var Generator[] $generators */
        $generators = [];

        foreach ($generatorClasses as $generatorClass) {
            $generators[$generatorClass] = $this->configuratorsLoader->loadFromGenerator($generatorClass);
        }

        return $generators;
    }

    /**
     * @param string[] $configuratorClasses
     */
    private function loadAdditionalConfigurators(array $configuratorClasses)
    {
        foreach ($configuratorClasses as $configuratorClass) {
            $this->configuratorsLoader->addConfiguratorClass($configuratorClass);
        }
    }

    /**
     * @param Args $args
     *
     * @return array
     */
    private function getConfiguration(Args $args, $profile)
    {
        $configurators = $this->configuratorsLoader->getConfigurators();

        $interactive = $this->config->get('interactive', true);
        $disableDefaults = $interactive && $args->getOption('no-default-loading');
        $defaults = $this->processSavedDefaults($profile, !$interactive || $disableDefaults);

        if ([] === $defaults && !$disableDefaults) {
            $defaults = $this->defaultsProcessor->process($profile);
        }

        if ($interactive) {
            $questioner = new InteractiveQuestioner($this->style);
        } else {
            $questioner = new UsingDefaultsQuestioner();
        }

        $questionsSet = $questioner->interact($configurators, !$args->getOption('all'), $defaults);

        if (!$disableDefaults) {
            file_put_contents(
                $this->config->get('current_dir').'/.dancer-defaults.json',
                json_encode(
                    [
                        'profile' => $profile,
                        'defaults' => $questionsSet->getAnswers(),
                    ],
                    JSON_PRETTY_PRINT
                )
            );
        }

        $values = $questionsSet->getValues();

        foreach ($configurators as $finalizer) {
            $finalizer->finalizeConfiguration($values);
        }

        return $values;
    }

    private function processSavedDefaults($profile, $disableDefaults = false)
    {
        if (!file_exists($file = $this->config->get('current_dir').'/.dancer-defaults.json')) {
            return [];
        }

        if ($disableDefaults) {
            unlink($file);

            return [];
        }

        $config = json_decode(file_get_contents($file), true, 512, JSON_BIGINT_AS_STRING);
        unlink($file);

        if (!is_array($config) || !isset($config['profile'], $config['defaults'])) {
            $this->style->warning('Cached defaults file is corrupted, ignoring.');

            return [];
        }

        if ($config['profile'] !== $profile) {
            $this->style->note('Cached defaults profile mismatch, ignoring.');

            return [];
        }

        // XXX Keep track of used configuration-file (and modification-time)

        $this->style->note(
            [
                'It seems the last execution in this directory did not complete successfully.',
                'Using cached defaults for the questioner defaults.'."\n".
                'When you provide other answers, some auto guessed values may not work as expected!',
                'Abort (ctrl + c) if you don\'t want to reuse the default values.',
            ]
        );

        return $config['defaults'];
    }

    private function executeGenerators(array $generators, array $configuration)
    {
        $i = 1;
        $total = count($generators);

        $this->style->text(
            ['', '<fg=green>Start dancing, this may take a while...</>', sprintf('Total of tasks: %d', $total), '']
        );

        foreach ($generators as $generator) {
            $this->style->writeln(sprintf(' [%d/%d] Running %s', $i, $total, get_class($generator)));

            $generator->generate($configuration);
            ++$i;
        }

        $this->style->success('Done, enjoy!');

        if (file_exists($file = $this->config->get('current_dir').'/.dancer-defaults.json')) {
            unlink($file);
        }

        return true;
    }
}
