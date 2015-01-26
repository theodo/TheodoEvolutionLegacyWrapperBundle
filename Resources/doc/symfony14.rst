Wrap symfony >=1.4
===================

Here is the basic configuration for a symfony >=1.4 application:
::

    theodo_evolution_legacy_wrapper:
        root_dir: %kernel.root_dir%/../legacy
        kernel:
            id: theodo_evolution_legacy_wrapper.legacy_kernel.symfony14
            options:
                application: frontend
                environment: '%kernel.environment%'
                debug:       '%kernel.debug%'


Update options in runtime
-------------------------
There is an event ``LegacyKernelEvents::BOOT`` being dispatched in from ``Symfony14Kernel::boot`` before legacy project is actually started.
Handling this event allows to update ``options`` (``application``, ``environment`` and ``debug``) in runtime.
For example to launch application(in terms of symfony 1) based on request:

::

    namespace My\LegacyWrapperBundle\EventListener;
    
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Event\LegacyKernelBootEvent;
    use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelEvents;
    
    class MyListener implements EventSubscriberInterface
    {
        /**
         * @param LegacyKernelBootEvent $event
         */
        public function onLegacyKernelBoot(LegacyKernelBootEvent $event)
        {
            $request = $event->getRequest();
            if($this->isBackendApplication()) {
                $event->setOption('application', 'backend');
            } else {
                $event->setOption('application', 'frontend');
            }
        }
    
        /**
         * {@inheritdoc}
         */
        public static function getSubscribedEvents()
        {
            return [
                LegacyKernelEvents::BOOT => 'onLegacyKernelBoot'
            ];
        }
    
    
        /**
         * @param Request $request
         *
         * @return bool
         */
        protected function isBackendApplication(Request $request)
        {
            // .... some logic there
        }
    }


