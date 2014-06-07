<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Tests\Kernel;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\CodeIgniterClassLoader;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\CodeIgniterKernel;

class CodeIgniterKernelTest extends ProphecyTestCase
{
    public function testShouldBoot()
    {
        $request = new Request();
        $requestStack = $this->prophesize('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->getCurrentRequest()->willReturn($request);

        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->get('request_stack')->willReturn($requestStack->reveal());
        $container->getParameter(Argument::any())->willReturn('foo');
        $container->set(Argument::type('string'), Argument::any())->shouldBeCalled();

        $classLoader = new CodeIgniterClassLoader();

        $kernel = new CodeIgniterKernel();
        $kernel->setRootDir($_ENV['THEODO_EVOLUTION_FAKE_PROJECTS'].'/codeigniter21');
        $kernel->setOptions(array('environment' => 'prod', 'version' => '2.1.4', 'core' => false));
        $kernel->setClassLoader($classLoader);

        $kernel->boot($container->reveal());

        $this->assertTrue($kernel->isBooted());
        $this->assertTrue($classLoader->isAutoloaded());
        // CodeIgniter creates a global variables
        $this->assertArrayHasKey('BM', $GLOBALS);
        $this->assertArrayHasKey('EXT', $GLOBALS);
        $this->assertArrayHasKey('CFG', $GLOBALS);
        $this->assertArrayHasKey('UNI', $GLOBALS);
        $this->assertArrayHasKey('URI', $GLOBALS);
        $this->assertArrayHasKey('RTR', $GLOBALS);
        $this->assertArrayHasKey('OUT', $GLOBALS);
        $this->assertArrayHasKey('SEC', $GLOBALS);
    }
}
 