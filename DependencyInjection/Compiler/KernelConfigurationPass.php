<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * KernelConfigurationPass
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class KernelConfigurationPass implements CompilerPassInterface
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
        $kernelId = $container->getParameter('theodo_evolution_legacy_wrapper.legacy_kernel.id');
        $kernelOptions = $container->getParameter('theodo_evolution_legacy_wrapper.legacy_kernel.options');
        $container->setAlias('theodo_evolution_legacy_wrapper.legacy_kernel', $kernelId);

        if (!empty($kernelOptions)) {
            $definition = $container->findDefinition($kernelId);
            $definition->addMethodCall('setOptions', array($kernelOptions));
        }
    }
}
 