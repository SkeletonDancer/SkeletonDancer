<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Generator;

use Rollerworks\Tools\SkeletonDancer\StringUtil;

final class SfBundleGenerator extends AbstractGenerator
{
    public function generate($name, $namespace, $bundleName, $configFormat, $workingDir)
    {
        $bundleAlias = $this->generateExtensionAlias($bundleName);
        $extensionName = $this->generateExtensionName($namespace, $bundleName);

        $this->filesystem->dumpFile(
            $workingDir.'/src/'.$bundleName.'.php',
            $this->twig->render(
                'SfBundle/bundle.php.twig',
                [
                    'name' => $name,
                    'namespace' => $namespace,
                    'bundle' => $bundleName,
                    'extension_name' => $extensionName,
                    'extension_alias' => $bundleAlias,
                ]
            )
        );

        $this->filesystem->dumpFile(
            $workingDir.'/src/DependencyInjection/'.$extensionName.'Extension.php',
            $this->twig->render(
                'SfBundle/Extension.php.twig',
                [
                    'name' => $name,
                    'namespace' => $namespace,
                    'bundle' => $bundleName,
                    'format' => $configFormat,
                    'extension_name' => $extensionName,
                    'extension_alias' => $bundleAlias,
                ]
            )
        );

        $this->filesystem->dumpFile(
            $workingDir.'/src/DependencyInjection/Configuration.php',
            $this->twig->render(
                'SfBundle/configuration.php.twig',
                [
                    'name' => $name,
                    'namespace' => $namespace,
                ]
            )
        );

        $this->filesystem->dumpFile(
            $workingDir.'/src/Resources/config/services/core.'.$configFormat,
            $this->twig->render(
                'SfBundle/services.'.$configFormat.'.twig',
                [
                    'namespace' => $namespace,
                    'extension_alias' => $bundleAlias,
                ]
            )
        );
    }

    private function generateExtensionName($namespace, $bundleName)
    {
        $namespace = str_replace('\\\\', '\\', $namespace);

        if (preg_match('/^(?P<vendor>\w+)\\\\(?:Bundle)?\\\\(?P<product>\w+)/', $namespace, $parts)) {
            $parts['product'] = substr($parts['product'], 0, -6);

            return $parts['product'];
        }

        return substr($bundleName, 0, -6);
    }

    private function generateExtensionAlias($bundleName)
    {
        return StringUtil::underscore(substr($bundleName, 0, -6));
    }
}
