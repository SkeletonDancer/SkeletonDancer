<?php

$header = <<<EOF
This file is part of the SkeletonDancer package.

(c) Sebastiaan Stok <s.stok@rollerscapes.net>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP70Migration' => true,
        'array_syntax' => array('syntax' => 'short'),
        'combine_consecutive_unsets' => true,
        'declare_strict_types' => true,
        //'doctrine_annotation_braces' => true,
        //'doctrine_annotation_indentation' => true,
        //'doctrine_annotation_spaces' => true,
        // one should use PHPUnit methods to set up expected exception instead of annotations
        //'general_phpdoc_annotation_remove' => ['expectedException', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp'],
        'header_comment' => ['header' => $header],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => true,
        'no_extra_consecutive_blank_lines' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block'],
        'no_short_echo_tag' => true,
        'no_unreachable_default_argument_value' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_no_empty_return' => false, // PHP 7 compatibility
        'phpdoc_order' => true,
        // This breaks for variable @var blocks
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => false,
        'semicolon_after_instruction' => true,
        'single_import_per_statement' => false,
        'strict_comparison' => true,
        'strict_param' => true,
    ))
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([__DIR__.'/src', __DIR__.'/tests'])
    )
;
