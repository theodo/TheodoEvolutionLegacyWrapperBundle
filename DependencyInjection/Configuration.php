<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('theodo_evolution_legacy_wrapper');

        $rootNode
            ->children()
                ->scalarNode('root_dir')
                    ->info('The path where the legacy app lives')
                    ->isRequired()
                ->end()
                ->scalarNode('kernel_id')->defaultValue('theodo_evolution_legacy_wrapper.legacy_kernel.symfony14')->end()
                ->scalarNode('class_loader_id')->isRequired()->end()
                ->arrayNode('assets')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('base')->isRequired()->end()
                            ->arrayNode('directories')
                            ->example(array('css', 'js', 'images'))
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return !is_array($v); })
                                ->then(function ($v) { return array($v); })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
