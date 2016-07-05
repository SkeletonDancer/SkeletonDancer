<?php

/*
 * This file is part of the SkeletonDancer package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Tools\SkeletonDancer\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('skeletondancer');

        $rootNode
            ->children()
                ->booleanNode('interactive')->defaultTrue()->end()
                ->enumNode('overwrite')->values(['abort', 'skip', 'force', 'ask', 'backup'])->defaultValue('ask')->end()
                ->append($this->addDefaultsNode())
                ->variableNode('profile_resolver')
                    ->validate()
                        ->ifTrue(
                            function ($value) {
                                return null !== $value && !is_array($value) && !is_string($value);
                            }
                        )
                        ->thenInvalid('Profile resolver needs to a be a string, array-map or null.')
                    ->end()
                ->end()
                ->arrayNode('autoloading')
                    ->normalizeKeys(false)
                    ->children()
                        ->arrayNode('psr-4')
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('name')
                            ->prototype('variable')
                                ->validate()
                                    ->ifTrue(
                                        function ($value) {
                                            return !is_array($value) && !is_string($value);
                                        }
                                    )
                                    ->thenInvalid('psr-4 mapping value needs to a be a string or array.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('files')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('expression_language')
                    ->children()
                        ->arrayNode('function_providers')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('profiles')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('description')->defaultValue('')->end()
                            ->arrayNode('generators')
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('import')
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                            ->end()
                            ->append($this->addDefaultsNode())
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function addDefaultsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('defaults');

        $node
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('scalar')->end()
        ;

        return $node;
    }
}
