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

namespace SkeletonDancer\Questioner;

use SkeletonDancer\Question;
use SkeletonDancer\Questioner;
use SkeletonDancer\QuestionsSet;
use SkeletonDancer\StringUtil;

class PhpNamespaceQuestioner implements Questioner
{
    public function interact(QuestionsSet $questions)
    {
        $questions->communicate(
            'namespace',
            Question::ask(
                'PHP Namespace',
                function (array $answers) {
                    return $this->getDefaultValue($answers);
                },
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

                    return $this->validateName($namespace);
                }
            )
        );
    }

    protected function getDefaultValue(array $answers): ?string
    {
        if (preg_match('/^(?P<vendor>[a-z0-9_.-]+)\s+(?P<name>[a-z0-9_.-]+)$/i', $answers['project_name'] ?? '', $regs)) {
            return StringUtil::camelize($regs[1]).'\\'.StringUtil::camelize($regs[2]);
        }

        return null;
    }

    protected function validateName(string $namespace): ?string
    {
        return $namespace;
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
