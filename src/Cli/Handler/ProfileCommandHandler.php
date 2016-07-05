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
use Rollerworks\Tools\SkeletonDancer\Configurator\Loader;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\IO\IO;

final class ProfileCommandHandler
{
    private $style;
    private $configuratorsLoader;
    private $config;
    private $bufferedOut;

    public function __construct(
        SymfonyStyle $style,
        Loader $configuratorsLoader,
        Config $config
    ) {
        $this->style = $style;
        $this->configuratorsLoader = $configuratorsLoader;
        $this->config = $config;

        $this->bufferedOut = new BufferedOutput(null, $this->style->isDecorated(), $this->style->getFormatter());
    }

    public function handleList(Args $args, IO $io)
    {
        $this->style->title('List profiles');

        if ($io->isVerbose()) {
            $this->style->text(
                [
                    sprintf('// Using config file: %s', $this->config->get('config_file', '')),
                    sprintf('// Project directory: %s', $this->config->get('project_directory', '')),
                    sprintf('// Dancer config directory: %s', $this->config->get('dancer_directory', '')),
                ]
            );
        }

        $profiles = $this->config->get(['profiles']);
        $verbose = $io->isVerbose();

        foreach ($profiles as $profileName => $profile) {
            $this->style->section($profileName);
            $this->renderProfile($profileName, $profile, $verbose);
        }

        return 0;
    }

    public function handleShow(Args $args, IO $io)
    {
        $this->style->title('Show profile information');

        if ($io->isVerbose()) {
            $this->style->text(
                [
                    sprintf('// Using config file: %s', $this->config->get('config_file', '')),
                    sprintf('// Project directory: %s', $this->config->get('project_directory', '')),
                    sprintf('// Dancer config directory: %s', $this->config->get('dancer_directory', '')),
                ]
            );
        }

        if (!$profileName = $args->getArgument('name')) {
            $profileName = $this->style->choice('Profile to show', array_keys($this->config->get(['profiles'])));
        }

        $profile = $this->config->get(['profiles', $profileName]);

        if (null === $profile) {
            $this->style->error(sprintf('Unable to find a profile with name "%s".', $profileName));

            return 1;
        }

        $this->style->section($profileName);
        $this->renderProfile($profileName, $profile, true);

        return 0;
    }

    private function renderProfile($profileName, array $profile, $verbose = false)
    {
        $row = [
            ['Description', $profile['description']],
            ['Import', $profile['import'] ? implode(', ', $profile['import']) : '[ ]'],
            ['Generators', $profile['generators'] ? '* '.implode("\n* ", $profile['generators']) : '[ ]'],
        ];

        if ($verbose) {
            $defaultsTable = [];

            foreach ($this->getProfileQuestionsAndDefaults($profileName) as $name => $value) {
                $defaultsTable[] = [$name, Yaml::dump($value, 4, 1)];
            }

            $row[] = ['Defaults', $this->detailsTable($defaultsTable, true)];
        }

        $this->style->write($this->detailsTable($row), false, SymfonyStyle::OUTPUT_RAW);
    }

    private function detailsTable(array $rows, $bordered = false)
    {
        if (!$rows) {
            return '';
        }

        $rows = array_map(
            function ($row) {
                $row[0] = sprintf('<info>%s:</info>', $row[0]);

                return $row;
            },
            $rows
        );

        $table = new Table($this->bufferedOut);
        $table->getStyle()
            ->setPaddingChar(' ')
            ->setHorizontalBorderChar($bordered ? '-' : '')
            ->setVerticalBorderChar(' ')
            ->setCrossingChar('-')
            ->setCellHeaderFormat('%s')
            ->setCellRowFormat('%s')
            ->setCellRowContentFormat('%s')
            ->setBorderFormat('%s')
            ->setPadType(STR_PAD_RIGHT)
        ;
        $table->setRows($rows);
        $table->render();

        return $this->bufferedOut->fetch();
    }

    // Same as \Rollerworks\Tools\SkeletonDancer\Generator\SkeletonDancerInitGenerator::getProfileQuestionsAndDefaults
    // Needs to be refactored to a class.
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

        return $questions->all();
    }
}
