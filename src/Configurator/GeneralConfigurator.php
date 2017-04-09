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
use Rollerworks\Tools\SkeletonDancer\Service\Git;
use Rollerworks\Tools\SkeletonDancer\StringUtil;

final class GeneralConfigurator implements Configurator
{
    /**
     * @var Git
     */
    private $git;

    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    public function interact(QuestionsSet $questions)
    {
        $questions->communicate('name', Question::ask('Name'));
        $questions->communicate(
            'package_name',
            Question::ask(
                'Package name (<vendor>/<name>)',
                function (array $config) {
                    if ('' !== (string) $config['name'] &&
                        preg_match('/^(?P<vendor>[a-z0-9_.-]+)\s+(?P<name>[a-z0-9_.-]+)$/i', $config['name'], $regs)
                    ) {
                        return mb_strtolower(StringUtil::humanize($regs[1]).'/'.StringUtil::humanize($regs[2]));
                    }
                },
                function ($name) {
                    if (!preg_match('{^[a-z0-9_.-]+/[a-z0-9_.-]+$}', $name)) {
                        throw new \InvalidArgumentException(
                            'The package name '.
                            $name.
                            ' is invalid, it should be lowercase and have a vendor name, a forward slash, and a package name, matching: [a-z0-9_.-]+/[a-z0-9_.-]+'
                        );
                    }

                    return $name;
                }
            )
        );

        $questions->communicate(
            'namespace',
            Question::ask(
                'PHP Namespace',
                null,
                function ($namespace) {
                    $namespace = trim(str_replace('/', '\\', $namespace), '\\');

                    if (!preg_match('/^(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\?)+$/', $namespace)) {
                        throw new \InvalidArgumentException('The namespace contains invalid characters.');
                    }

                    // validate reserved keywords
                    $reserved = self::getReservedWords();

                    foreach (explode('\\', $namespace) as $word) {
                        if (in_array(mb_strtolower($word), $reserved, true)) {
                            throw new \InvalidArgumentException(
                                sprintf('The namespace cannot contain PHP reserved words ("%s").', $word)
                            );
                        }
                    }

                    return $namespace;
                }
            )
        );

        $questions->communicate(
            'author_name',
            Question::ask(
                'Author name',
                function () {
                    return $this->git->getGitConfig('user.name', 'global');
                }
            )
        );

        $questions->communicate(
            'author_email',
            Question::ask(
                'Author e-mail',
                function () {
                    return $this->git->getGitConfig('user.email', 'global');
                }
            )
        );

        $questions->communicate('php_min', Question::ask('Php-min', mb_substr(PHP_VERSION, 0, 3)));
        $questions->communicate(
            'src_dir',
            Question::ask(
                'PHP source directory',
                'src',
                function ($value) {
                    return trim($value, '/');
                }
            )->markOptional('.')
        );
    }

    public function finalizeConfiguration(array &$configuration)
    {
        if ('' === (string) $configuration['src_dir']) {
            $configuration['src_dir_norm'] = '';
        } else {
            $configuration['src_dir_norm'] = $configuration['src_dir'].'/';
        }
    }

    private static function getReservedWords()
    {
        return [
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'do',
            'else',
            'elseif',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'extends',
            'final',
            'finally',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'interface',
            'instanceof',
            'insteadof',
            'namespace',
            'new',
            'or',
            'private',
            'protected',
            'public',
            'static',
            'switch',
            'throw',
            'trait',
            'try',
            'use',
            'var',
            'while',
            'xor',
            'yield',
            '__CLASS__',
            '__DIR__',
            '__FILE__',
            '__LINE__',
            '__FUNCTION__',
            '__METHOD__',
            '__NAMESPACE__',
            '__TRAIT__',
            '__halt_compiler',
            'die',
            'echo',
            'empty',
            'exit',
            'eval',
            'include',
            'include_once',
            'isset',
            'list',
            'require',
            'require_once',
            'return',
            'print',
            'unset',
        ];
    }
}
