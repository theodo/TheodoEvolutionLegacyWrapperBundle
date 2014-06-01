<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ReplaceRouterPass
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class ReplaceRouterPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (true === $container->hasDefinition('theodo_evolution_legacy_wrapper.router_listener')) {
            $routerListener = $container->getDefinition('router_listener');

            $definition = $container->getDefinition('theodo_evolution_legacy_wrapper.router_listener');
            $definition->replaceArgument(1, $routerListener);

            $container->setAlias('router_listener', 'theodo_evolution_legacy_wrapper.router_listener');
        }
    }
}