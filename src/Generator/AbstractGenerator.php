<?php

namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractGenerator
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    protected function generateComposerName($namespace)
    {
        $namespace = str_replace('\\\\', '\\', $namespace);

        if (preg_match('/^(?P<vendor>\w+)\\\\(?:Bundle|Tools|Components?)?\\\\(?P<product>\w+)/', $namespace, $parts)) {
            if ('Bundle' === substr($parts['product'], -6)) {
                $parts['product'] = substr($parts['product'], 0, -6);
            }

            return strtolower($parts['vendor'].'/'.$parts['product']);
        }
    }
}
