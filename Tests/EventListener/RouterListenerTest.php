<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Tests\EventListener;

use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\EventListener\RouterListener;

/**
 * RouterListenerTest
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class RouterListenerTest extends ProphecyTestCase
{
    public function testLegacyKernelShouldHandleTheRequest()
    {
        $request = new Request();
        $response = new Response();

        $httpKernel = $this->prophesize('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($httpKernel->reveal(), $request, HttpKernel::MASTER_REQUEST);

        $baseRouterListener = $this->prophesize('Symfony\Component\HttpKernel\EventListener\RouterListener');
        $baseRouterListener->onKernelRequest($event)->willThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $kernel = $this->prophesize('Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface');
        $kernel->handle($request, HttpKernel::MASTER_REQUEST, true)->willReturn($response);

        $routerListener = new \Theodo\Evolution\Bundle\LegacyWrapperBundle\EventListener\RouterListener($kernel->reveal(), $baseRouterListener->reveal());
        $routerListener->onKernelRequest($event);

        $this->assertEquals($response, $event->getResponse());
    }

    public function testLegacyKernelShouldNotHandleTheRequest()
    {
        $request = new Request();

        $httpKernel = $this->prophesize('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($httpKernel->reveal(), $request, HttpKernel::MASTER_REQUEST);

        $baseRouterListener = $this->prophesize('Symfony\Component\HttpKernel\EventListener\RouterListener');
        $baseRouterListener->onKernelRequest($event);

        $kernel = $this->prophesize('Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface');
        $kernel->handle()->shouldNotBeCalled();

        $routerListener = new RouterListener($kernel->reveal(), $baseRouterListener->reveal());
        $routerListener->onKernelRequest($event);
    }

    public function testNotFoundHttp()
    {
        $request = new Request();
        $response = new Response();
        $response->setStatusCode(404);

        $httpKernel = $this->prophesize('Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = new GetResponseEvent($httpKernel->reveal(), $request, HttpKernel::MASTER_REQUEST);

        $baseRouterListener = $this->prophesize('Symfony\Component\HttpKernel\EventListener\RouterListener');
        $baseRouterListener->onKernelRequest($event)->willThrow('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $kernel = $this->prophesize('Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface');
        $kernel->handle($request, HttpKernel::MASTER_REQUEST, true)->willReturn($response);

        $routerListener = new RouterListener($kernel->reveal(), $baseRouterListener->reveal());
        $routerListener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }
}
