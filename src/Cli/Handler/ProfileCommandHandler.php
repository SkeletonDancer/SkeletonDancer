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

namespace Rollerworks\Tools\SkeletonDancer\Cli\Handler;

use Rollerworks\Tools\SkeletonDancer\AnswersSet;
use Rollerworks\Tools\SkeletonDancer\Configuration\Config;
use Rollerworks\Tools\SkeletonDancer\Configuration\ProfileConfigResolver;
use Rollerworks\Tools\SkeletonDancer\Profile;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use Rollerworks\Tools\SkeletonDancer\ResolvedProfile;
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
    private $config;
    private $bufferedOut;

    /**
     * @var ProfileConfigResolver
     */
    private $profileConfigResolver;

    public function __construct(
        SymfonyStyle $style,
        ProfileConfigResolver $profileConfigResolver,
        Config $config
    ) {
        $this->style = $style;
        $this->profileConfigResolver = $profileConfigResolver;
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
                    sprintf('// Dancer config directory: %s', $this->config->get('dancer_directory', '')),
                    sprintf('// Project directory: %s', $this->config->get('project_directory', '')),
                ]
            );
        }

        $profiles = $this->config->getProfiles();
        $verbose = $io->isVerbose();

        foreach ($profiles as $profileName => $profile) {
            $this->style->section($profileName);
            $this->renderSimpleProfile($profile, $verbose);
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

        $profileNames = array_keys($this->config->getProfiles());

        if (!$profileName = $args->getArgument('name')) {
            $profileName = $this->style->choice('Profile to show', $profileNames);
        }

        if (null === $profileName) {
            $this->style->error(sprintf('Unable to find a profile with name "%s".', $profileName));

            return 1;
        }

        $this->style->section($profileName);
        $this->renderResolvedProfile($this->config->getProfiles()[$profileName], $io->isVerbose());

        return 0;
    }

    private function renderResolvedProfile(Profile $profile, $verbose = false)
    {
        $profileConfig = $this->getProfileResolvedConfig($profile->name);

        $row = [
            ['Description', $profile->description],
            ['Import', $profile->imports ? implode(', ', $profile->imports) : '[ ]'],
            ['Generators', $profileConfig->generators ? '- '.implode("\n- ", array_map('get_class', $profileConfig->generators)) : '[ ]'],
            ['Configurators', $profileConfig->configurators ? '- '.implode("\n- ", array_map('get_class', $profileConfig->configurators)) : '[ ]'],
        ];

        if ($verbose) {
            $variablesTable = [];
            $defaultsTable = [];

            foreach ($profileConfig->variables as $name => $value) {
                $variablesTable[] = [$name, Yaml::dump($value, 4, 1)];
            }

            foreach ($profileConfig->defaults as $name => $value) {
                $defaultsTable[] = [$name, Yaml::dump($value, 4, 1)];
            }

            $row[] = ['Variables', $this->detailsTable($variablesTable, true)];
            $row[] = ['Defaults', $this->detailsTable($defaultsTable, true)];
        }

        $this->style->write($this->detailsTable($row), false, SymfonyStyle::OUTPUT_RAW);
    }

    private function renderSimpleProfile(Profile $profile, $verbose = false)
    {
        $row = [
            ['Description', $profile->description],
            ['Import', $profile->imports ? implode(', ', $profile->imports) : '[ ]'],
            ['Generators', $profile->generators ? '- '.implode("\n- ", $profile->generators) : '[ ]'],
            ['Configurators', $profile->configurators ? '- '.implode("\n- ", $profile->configurators) : '[ ]'],
        ];

        if ($verbose) {
            $variablesTable = [];
            $defaultsTable = [];

            foreach ($profile->variables as $name => $value) {
                $variablesTable[] = [$name, Yaml::dump($value, 4, 1)];
            }

            foreach ($profile->defaults as $name => $value) {
                $defaultsTable[] = [$name, Yaml::dump($value, 4, 1)];
            }

            $row[] = ['Variables', $this->detailsTable($variablesTable, true)];
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

    private function getProfileResolvedConfig(string $profile): ResolvedProfile
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

        return $profileConfig;
    }
}
