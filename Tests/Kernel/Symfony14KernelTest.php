<?php

namespace Kernel;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Symfony14Kernel;

/**
 * Symfony14KernelTest
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14KernelTest extends ProphecyTestCase
{
    public function testShouldBoot()
    {
        $classLoader = $this->prophesize('Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface');
        $container   = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');

        $classLoader->setKernel(Argument::type('Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Symfony14Kernel'))->shouldBeCalled();
        $classLoader->isAutoloaded()->willReturn(false);
        $classLoader->autoload()->shouldBeCalled();

        $kernel = new Symfony14Kernel();
        $kernel->setRootDir(__DIR__.'/fixtures/symfony14');
        $kernel->setClassLoader($classLoader->reveal());
        $kernel->setOptions(array(
            'application' => 'frontend',
            'environment' => 'dev',
            'debug' => true
        ));
        $kernel->boot($container->reveal());

        $this->assertTrue($kernel->isBooted());
    }
}
 