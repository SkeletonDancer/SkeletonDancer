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

namespace Dance\Generator;

use SkeletonDancer\Generator;
use SkeletonDancer\Service\Filesystem;

final class LicenseGenerator implements Generator
{
    private $twig;
    private $filesystem;

    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    public function generate(array $answers)
    {
        if (!isset($answers['license'])) {
            return self::STATUS_FAILURE;
        }

        $filename = 'licenses/'.mb_strtolower(str_replace(['+', '-Clause'], '', $answers['license'])).'.txt';

        if ($this->twig->getLoader()->exists($filename.'.twig')) {
            $content = $this->twig->render(
                $filename.'.twig',
                [
                    'author' => [
                        'name' => $answers['author_name'],
                        'email' => $answers['author_email'],
                    ],
                ]
            );
        } else {
            $content = file_get_contents(__DIR__.'/../../templates/'.$filename);
        }

        $this->filesystem->dumpFile('LICENSE', $content);
    }
}
