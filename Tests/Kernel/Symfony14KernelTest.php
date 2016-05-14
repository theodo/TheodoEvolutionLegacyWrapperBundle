<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Tests\Kernel;

use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Event\LegacyKernelBootEvent;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Symfony14Kernel;

/**
 * Symfony14KernelTest.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14KernelTest extends ProphecyTestCase
{
    public function testShouldBootAndHandleRequest()
    {
        $kernelOptions = array(
            'application' => 'frontend',
            'environment' => 'prod',
            'debug' => true,
        );

        $classLoader = $this->prophesize('Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface');

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/');
        $request->setSession($session);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->dispatch('legacy_kernel.boot', new LegacyKernelBootEvent($request, $kernelOptions))->shouldBeCalled();

        $container = $this->createProphesizedContainer($request);
        $container->get('session')->willReturn($session);
        $container->get('event_dispatcher')->willReturn($eventDispatcher);

        $classLoader->setKernel(Argument::type('Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Symfony14Kernel'))->shouldBeCalled();
        $classLoader->isAutoloaded()->willReturn(false);
        $classLoader->autoload()->shouldBeCalled();

        $kernel = new Symfony14Kernel();
        $kernel->setRootDir($_ENV['THEODO_EVOLUTION_FAKE_PROJECTS'].'/symfony14');
        $kernel->setClassLoader($classLoader->reveal());
        $kernel->setOptions($kernelOptions);

        $kernel->boot($container->reveal());
        $this->assertTrue($kernel->isBooted());

        $response = $kernel->handle($request, 1, true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShouldBootAndHandleRequestIfClassLoaderIsNotProvided()
    {
        $kernelOptions = array(
            'application' => 'frontend',
            'environment' => 'prod',
            'debug' => true,
        );

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/');
        $request->setSession($session);

        $eventDispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->dispatch('legacy_kernel.boot', new LegacyKernelBootEvent($request, $kernelOptions))->shouldBeCalled();

        $container = $this->createProphesizedContainer($request);
        $container->get('event_dispatcher')->willReturn($eventDispatcher);

        $kernel = new Symfony14Kernel();
        $kernel->setRootDir($_ENV['THEODO_EVOLUTION_FAKE_PROJECTS'].'/symfony14');
        $kernel->setOptions($kernelOptions);

        $kernel->boot($container->reveal());
        $this->assertTrue($kernel->isBooted());
        $this->assertAttributeEmpty('classLoader', $kernel);

        $response = $kernel->handle($request, 1, true);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShouldCopyLegacyResponseData()
    {
        $kernel = new Symfony14Kernel();
        $rm = new \ReflectionMethod($kernel, 'convertResponse');
        $rm->setAccessible(true);

        $legacyResponse = $this->prophesize('sfWebResponse');
        $legacyResponse->getHttpHeaders()->willReturn($headers = array(
            'Content-Type' => 'text/html',
            'Location' => 'http://localhost',
            'X-Custom-Header' => 'value',
        ));
        $legacyResponse->getContent()->willReturn($content = 'content');
        $legacyResponse->getCharset()->willReturn($charset = 'charset');
        $legacyResponse->getStatusCode()->willReturn($status = 200);

        /** @var Response $response */
        $response = $rm->invoke($kernel, $legacyResponse->reveal());

        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($charset, $response->getCharset());
        $this->assertEquals($content, $response->getContent());
        $this->assertEquals($headers['Content-Type'], $response->headers->get('Content-Type'));
        $this->assertEquals($headers['Location'], $response->headers->get('Location'));
        $this->assertEquals($headers['X-Custom-Header'], $response->headers->get('X-Custom-Header'));
    }
    
    protected function createProphesizedContainer($request)
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');

        if (Kernel::MAJOR_VERSION === 2) {
            $container->has('request_stack')->willReturn(false);
            $container->get('request')->willReturn($request);
        } elseif (Kernel::MAJOR_VERSION === 3) {
            $request_stack = new RequestStack();
            $request_stack->push($request);
            $container->has('request_stack')->willReturn(true);
            $container->get('request_stack')->willReturn($request_stack);
        } else {
            $container->has('request_stack')->willReturn(false);
            $container->get('request')->willReturn($request);
        }
        
        return $container;
    }
}
