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
                ->append($this->addKernelNode())
                ->scalarNode('class_loader_id')->end()
                ->arrayNode('assets')
                    ->canBeUnset()
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
                                ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    public function addKernelNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('kernel');

        $node
            ->example('theodo_evolution_legacy_wrapper.legacy_kernel.symfony14')
            ->beforeNormalization()
                ->ifString()
                ->then(function($v) { return array('id'=> $v); })
            ->end()
            ->children()
                ->scalarNode('id')->end()
                ->arrayNode('options')
                    ->children()
                        // Symfony 1.4 options
                        ->scalarNode('application')->end()
                        ->scalarNode('environment')->end()
                        ->scalarNode('debug')->end()
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    // Validate symfony 1 configuration
                    if (strpos($v['id'], 'symfony1')) {
                        return !isset(
                            $v['options']['application'],
                            $v['options']['environment'],
                            $v['options']['debug']
                        );
                    }
                })
                ->thenInvalid('To use the symfony1 kernel you must set an application, environment and debug options.')
            ->end()
            ->validate()
                ->ifTrue(function($v) {
                    // Validate CodeIgniter configuration
                    if (strpos($v['id'], 'codeigniter')) {
                        return !isset(
                            $v['options']['environment'],
                            $v['options']['version'],
                            $v['options']['core']
                        );
                    }
                })
                ->thenInvalid('To use the codeigniter kernel you must set an environment a version and a core options.')
            ->end()
        ;

        return $node;
    }
}
