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

namespace Rollerworks\Tools\SkeletonDancer\Configurator;

use Rollerworks\Tools\SkeletonDancer\Configurator;
use Rollerworks\Tools\SkeletonDancer\Question;
use Rollerworks\Tools\SkeletonDancer\QuestionsSet;
use SLLH\StyleCIFixers\Fixers;

final class PhpCsConfigurator implements Configurator
{
    public function interact(QuestionsSet $questions)
    {
        $questions->communicate(
            'php_cs_preset',
            Question::choice('PHP-CS Preset', ['none', 'psr1', 'psr2', 'symfony', 'laravel', 'recommended'], 'symfony')
        );

        $questions->communicate(
            'php_cs_enabled_fixers',
            Question::ask(
                'PHP-CS Fixers',
                'none',
                function ($value) {
                    if ('none' === $value) {
                        return $value;
                    }

                    $fixers = $this->splitValues($value);
                    $fixers = array_unique($fixers);

                    foreach ($fixers as $fixer) {
                        $fixer = trim($fixer);

                        if (!in_array($fixer, Fixers::$valid, true)) {
                            throw new \InvalidArgumentException(sprintf('Fixer "%s" is not supported.', $fixer));
                        }

                        if (isset(Fixers::$conflicts[$fixer]) &&
                            $conflicts = array_intersect((array) Fixers::$conflicts[$fixer], $fixers)
                        ) {
                            throw new \InvalidArgumentException(
                                sprintf('Fixer "%s" conflicts with fixers "%s".', $fixer, implode('", "', $conflicts))
                            );
                        }

                        // XXX Need to validate 'here' if the enabled fixers conflict with the preset.
                    }

                    return $value;
                }
            )->markOptional('none') // Optional, as this should be configured using a defaults value.
        );

        $questions->communicate(
            'php_cs_disabled_fixers',
            Question::ask(
                'PHP-CS Disabled fixers',
                'none',
                function ($value) {
                    if ('none' === $value) {
                        return $value;
                    }

                    $fixers = $this->splitValues($value);
                    $fixers = array_unique($fixers);

                    foreach ($fixers as $fixer) {
                        $fixer = trim($fixer);

                        if (!in_array($fixer, Fixers::$valid, true)) {
                            throw new \InvalidArgumentException(sprintf('Fixer "%s" is not supported.', $fixer));
                        }
                    }

                    return $value;
                }
            )->markOptional('none') // Optional, as this should be configured using a defaults value.
        );

        $questions->communicate(
            'php_cs_linting',
            Question::confirm('Enable PHP-CS linting?', false)
        );

        if ($questions->communicate(
            'styleci_enabled',
            Question::confirm('Enable StyleCI (local configuration)?', true)
        )
        ) {
            $questions->set('php_cs_version_1_bc', false);
            $questions->communicate('php_cs_styleci_bridge', Question::confirm('Enable StyleCI bridge', true));
        } else {
            $questions->set('php_cs_styleci_bridge', false);
            $questions->communicate('php_cs_version_1_bc', Question::confirm('Enable PHP-CS v1 compatibility?', true));
        }

        // Finder questions.
        $questions->communicate('php_cs_finder_path', Question::ask('PHP-CS Finder {path}', 'src', false)->markOptional());
        $questions->communicate('php_cs_finder_not_path', Question::ask('PHP-CS Finder {not path}', '!*', false)->markOptional('!*'));
        $questions->communicate('php_cs_finder_exclude', Question::ask('PHP-CS Finder {exclude dirs}', '!*', false)->markOptional('!*'));

        $questions->communicate('php_cs_finder_name', Question::ask('PHP-CS Finder {name}', '*', false)->markOptional('*'));
        $questions->communicate('php_cs_finder_not_name', Question::ask('PHP-CS Finder {not name}', '!*', false)->markOptional('!*'));

        $questions->communicate('php_cs_finder_contains', Question::ask('PHP-CS Finder {contains}', '*', false)->markOptional('*'));
        $questions->communicate('php_cs_finder_not_contains', Question::ask('PHP-CS Finder {not contains}', '!*', false)->markOptional('!*'));

        $questions->communicate('php_cs_finder_depth', Question::ask('PHP-CS Finder {depth}', '*', false)->markOptional('*'));
    }

    public function finalizeConfiguration(array &$configuration)
    {
        if ($configuration['php_cs_styleci_bridge']) {
            $this->processWithBridgeEnabled($configuration);
        } else {
            $this->processWithBridgeDisabled($configuration);
        }

        $configuration['php_cs_finder'] = [
            'path' => $this->splitValues($configuration['php_cs_finder_path']),
            'not_path' => $this->splitValues($configuration['php_cs_finder_not_path']),
            'exclude' => $this->splitValues($configuration['php_cs_finder_exclude']),
            'name' => $this->splitValues($configuration['php_cs_finder_name']),
            'not_name' => $this->splitValues($configuration['php_cs_finder_not_name']),
            'contains' => $this->splitValues($configuration['php_cs_finder_contains']),
            'not_contains' => $this->splitValues($configuration['php_cs_finder_not_contains']),
            'depth' => $this->splitValues($configuration['php_cs_finder_depth']),
        ];
    }

    private function splitValues($value)
    {
        if ('' === $value || null === $value) {
            return [];
        }

        return preg_split('/\s*,\s*/', $value);
    }

    private function resolveAliases(array $fixers, $version = 2)
    {
        if (!count($fixers)) {
            return $fixers;
        }

        $aliases = 1 === $version ? array_flip(Fixers::$aliases) : Fixers::$aliases;

        foreach ($fixers as $i => $fixer) {
            if (isset($aliases[$fixer])) {
                $fixers[$i] = $aliases[$fixer];
            }
        }

        return $fixers;
    }

    private function processWithBridgeEnabled(array &$configuration)
    {
        $configuration['composer']['require-dev'][] = 'sllh/php-cs-fixer-styleci-bridge';

        $fixers = $this->resolveAliases($this->splitValues($configuration['php_cs_enabled_fixers']));
        $preset = mb_strtolower($configuration['php_cs_preset']).'_fixers';

        if ('none' !== $configuration['php_cs_preset']) {
            foreach (array_intersect($fixers, Fixers::${$preset}) as $i => $fixer) {
                unset($fixers[$i]);
            }
        }

        $configuration['php_cs_enabled_fixers'] = $fixers = array_unique($fixers);
        $configuration['php_cs_disabled_fixers'] = array_unique(
            $this->resolveAliases($this->splitValues($configuration['php_cs_disabled_fixers']))
        );

        $configuration['php_cs_level'] = $configuration['php_cs_preset'];
        $configuration['php_cs_enabled_fixers_v1'] = [];
        $configuration['php_cs_disabled_fixers_v1'] = [];
    }

    private function processWithBridgeDisabled(array &$configuration)
    {
        $fixers = $this->resolveAliases($this->splitValues($configuration['php_cs_enabled_fixers']));
        $preset = mb_strtolower($configuration['php_cs_preset']).'_fixers';

        // When no level is available for the preset set the level to none and merge fixers
        // of the preset with the enabled fixers.
        if (in_array($configuration['php_cs_preset'], ['laravel', 'recommended'], true)) {
            $configuration['php_cs_enabled_fixers'] = array_unique(array_merge($fixers, Fixers::${$preset}));
            $configuration['php_cs_level'] = 'none';
        } else {
            $configuration['php_cs_level'] = $configuration['php_cs_preset'];
        }

        // Only when level is not available remove overlapping fixers.
        if ('none' !== $configuration['php_cs_level']) {
            foreach (array_intersect($fixers, Fixers::${$preset}) as $i => $fixer) {
                unset($fixers[$i]);
            }
        }

        $configuration['php_cs_enabled_fixers'] = $fixers = array_unique($fixers);
        $configuration['php_cs_disabled_fixers'] = array_unique(
            $this->resolveAliases($this->splitValues($configuration['php_cs_disabled_fixers']))
        );

        if ($configuration['php_cs_version_1_bc']) {
            $configuration['php_cs_enabled_fixers_v1'] = $this->resolveAliases($fixers, 1);
            $configuration['php_cs_disabled_fixers_v1'] = $this->resolveAliases($configuration['php_cs_disabled_fixers'], 1);
        } else {
            $configuration['php_cs_enabled_fixers_v1'] = [];
            $configuration['php_cs_disabled_fixers_v1'] = [];
        }
    }
}
