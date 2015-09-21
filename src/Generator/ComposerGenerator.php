<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Symfony\Component\Filesystem\Filesystem;

final class ComposerGenerator
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    public function generate($namespace, $type, $license, $author, $phpMin, $workingDir)
    {
        $packageName = $this->generateComposerName($namespace);

        $this->filesystem->dumpFile(
            $workingDir.'/composer.json',
            $this->twig->render(
                'composer.json.twig',
                [
                    'name' => $packageName,
                    'type' => $type,
                    'license' => $license,
                    'author' => $this->extractAuthor($author),
                    'phpMin' => $phpMin,
                    'namespace' => $namespace,
                ]
            )
        );
    }

    private function extractAuthor($author)
    {
        return [
            'name' => substr($author, 0, strpos($author, '<')),
            'email' => substr($author, strpos($author, '<') + 1, strrpos($author, '>')),
        ];
    }

    private function generateComposerName($namespace)
    {
        if (!preg_match('/^(?P<vendor>\w+)\\\\(Bundle|Tools|Components?)?\\\\(?P<product>\w+)/', $namespace, $parts)) {
            if ('Bundle' === substr($parts['product'], -6)) {
                $parts['product'] = substr($parts['product'], 0, -6);
            }

            return strtolower($parts['vendor'].'/'.$parts['product']);
        }
    }
}
