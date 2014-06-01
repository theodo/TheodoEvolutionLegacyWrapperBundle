<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener as SymfonyRouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * RouterListener delegates the request handling to the Symfony router listener.
 * If the later does not match any controller, then this listener catches the NotFoundHttpException
 * and tells the wrapper to handle the request instead.
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class RouterListener implements EventSubscriberInterface
{
    /**
     * @var \Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface
     */
    protected $legacyKernel;

    /**
     * @var RouterListener
     */
    protected $routerListener;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param HttpKernelInterface $legacyKernel
     * @param SymfonyRouterListener $routerListener
     * @param LoggerInterface $logger
     */
    public function __construct(HttpKernelInterface $legacyKernel, SymfonyRouterListener $routerListener, LoggerInterface $logger = null)
    {
        $this->legacyKernel = $legacyKernel;
        $this->routerListener = $routerListener;
        $this->logger = $logger;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return GetResponseEvent
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        try {
            $this->routerListener->onKernelRequest($event);
        } catch (NotFoundHttpException $e) {
            if (null !== $this->logger) {
                $this->logger->info('Request handled by the '.$this->legacyKernel->getName().' wrapper.');
            }

            $response = $this->legacyKernel->handle($event->getRequest(), $event->getRequestType(), true);
            if ($response->getStatusCode() !== 404) {
                $event->setResponse($response);

                return $event;
            }
        }
    }

    /**
     * @param FinishRequestEvent $event
     */
    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
        $this->routerListener->onKernelFinishRequest($event);
    }

    /**
     * @{inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 31)),
            KernelEvents::FINISH_REQUEST => array(array('onKernelFinishRequest', 0)),
        );
    }
}