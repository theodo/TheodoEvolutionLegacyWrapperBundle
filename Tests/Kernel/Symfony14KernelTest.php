<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Tests\Kernel;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Symfony14Kernel;

/**
 * Symfony14KernelTest
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14KernelTest extends ProphecyTestCase
{
    public function testShouldBootAndHandleRequest()
    {
        $classLoader = $this->prophesize('Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface');

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/');
        $request->setSession($session);

        $container   = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->get('session')->willReturn($session);

        $classLoader->setKernel(Argument::type('Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Symfony14Kernel'))->shouldBeCalled();
        $classLoader->isAutoloaded()->willReturn(false);
        $classLoader->autoload()->shouldBeCalled();

        $kernel = new Symfony14Kernel();
        $kernel->setRootDir($_ENV['THEODO_EVOLUTION_FAKE_PROJECTS'].'/symfony14');
        $kernel->setClassLoader($classLoader->reveal());
        $kernel->setOptions(array(
            'application' => 'frontend',
            'environment' => 'prod',
            'debug' => true
        ));

        $kernel->boot($container->reveal());
        $this->assertTrue($kernel->isBooted());

        $response = $kernel->handle($request, 1, true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
 
