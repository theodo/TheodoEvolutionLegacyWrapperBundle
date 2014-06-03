<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface;

/**
 * LegacyBooterListener autoloads the legacy.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LegacyBooterListener implements EventSubscriberInterface
{
    /**
     * @var \Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @param LegacyKernelInterface $kernel
     * @param ContainerInterface $container
     */
    public function __construct(LegacyKernelInterface $kernel, ContainerInterface $container)
    {
        $this->kernel = $kernel;
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->kernel->isBooted()) {
            $this->kernel->boot($this->container);
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', '35')
        );
    }

}
 