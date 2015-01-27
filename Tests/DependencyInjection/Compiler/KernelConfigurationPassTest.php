<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class KernelConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldConfigureKernelBasedOnParameters()
    {
        $container = new ContainerBuilder();

        $container->setDefinition('legacy_kernel_id', new Definition());

        $container->setParameter('theodo_evolution_legacy_wrapper.legacy_kernel.id', $kernel = 'legacy_kernel_id');
        $container->setParameter('theodo_evolution_legacy_wrapper.legacy_kernel.options', $options = array('key' => 'value'));
        $container->setParameter('theodo_evolution_legacy_wrapper.autoload.class_loader.id', $loader = 'class_loader');

        $compiler = new KernelConfigurationPass();
        $compiler->process($container);

        $kernelDefinition = $container->findDefinition('theodo_evolution_legacy_wrapper.legacy_kernel');

        $this->assertSame($container->findDefinition('legacy_kernel_id'), $kernelDefinition, 'Legacy kernel alias has not been set correctly');
        $this->assertAttributeContains(array('setOptions', array($options)), 'calls', $kernelDefinition, 'Method setOptions has not been set to call');
        $this->assertAttributeContains(array('setClassLoader', array(new Reference('class_loader'))), 'calls', $kernelDefinition, 'Method setClassLoader has not been set to call');
    }

    public function testShouldNotCallSetOptionsIfEmpty()
    {
        $container = new ContainerBuilder();

        $container->setDefinition('legacy_kernel_id', new Definition());

        $container->setParameter('theodo_evolution_legacy_wrapper.legacy_kernel.id', $kernel = 'legacy_kernel_id');
        $container->setParameter('theodo_evolution_legacy_wrapper.legacy_kernel.options', $options = array());
        $container->setParameter('theodo_evolution_legacy_wrapper.autoload.class_loader.id', $loader = 'class_loader');

        $compiler = new KernelConfigurationPass();
        $compiler->process($container);

        $kernelDefinition = $container->findDefinition('theodo_evolution_legacy_wrapper.legacy_kernel');

        $this->assertSame($container->findDefinition('legacy_kernel_id'), $kernelDefinition, 'Legacy kernel alias has not been set correctly');
        $this->assertAttributeNotContains(array('setOptions', array($options)), 'calls', $kernelDefinition, 'Method setOptions should not be set to call');
    }

    public function testShouldNotCallSetClassLoaderIfNull()
    {
        $container = new ContainerBuilder();

        $container->setDefinition('legacy_kernel_id', new Definition());

        $container->setParameter('theodo_evolution_legacy_wrapper.legacy_kernel.id', $kernel = 'legacy_kernel_id');
        $container->setParameter('theodo_evolution_legacy_wrapper.legacy_kernel.options', $options = array('key' => 'value'));
        $container->setParameter('theodo_evolution_legacy_wrapper.autoload.class_loader.id', $loader = null);

        $compiler = new KernelConfigurationPass();
        $compiler->process($container);

        $kernelDefinition = $container->findDefinition('theodo_evolution_legacy_wrapper.legacy_kernel');

        $this->assertSame($container->findDefinition('legacy_kernel_id'), $kernelDefinition, 'Legacy kernel alias has not been set correctly');
        $this->assertAttributeNotContains(array('setClassLoader', array(new Reference('class_loader'))), 'calls', $kernelDefinition, 'Method setClassLoader should not be set to call');
    }
}
