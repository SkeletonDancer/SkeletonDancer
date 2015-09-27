
<?php

$header = <<<EOF
This file is part of the RollerworksSkeletonDancerBundle package.

(c) Sebastiaan Stok <s.stok@rollerscapes.net>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->setUsingLinter(false)
    // use SYMFONY_LEVEL:
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    // and extra fixers:
    ->fixers([
        'ordered_use',
        //'strict',
        'strict_param',
        'short_array_syntax',
        'phpdoc_order',
        'header_comment',
        '-psr0',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in([__DIR__.'/src', __DIR__.'/tests'])
            ->exclude(['doc', 'spec'])
    )
;
