<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Tests\Kernel;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\CodeIgniterClassLoader;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\CodeIgniterKernel;

class CodeIgniterKernelTest extends ProphecyTestCase
{
    public function testShouldBootAndHandleRequest()
    {
        if (version_compare(PHP_VERSION, '5.6', '>=')) {
            $this->markTestSkipped('CodeIgniter v2.1 is not compatible with PHP >= 5.6');
        }

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/welcome/');
        $request->setSession($session);

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

        $response = $kernel->handle($request, 1, true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
 