<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Event\LegacyKernelBootEvent;

/**
 * Symfony14Kernel kernel handles.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14Kernel extends LegacyKernel
{
    /**
     * @var \sfApplicationConfiguration
     */
    private $configuration;

    /**
     * {@inheritdoc}
     */
    public function boot(ContainerInterface $container)
    {
        if (empty($this->options)) {
            throw new \RuntimeException('You must provide options for the Symfony 1.4 kernel.');
        }

        if ($this->isBooted()) {
            return;
        }

        if ($this->classLoader && !$this->classLoader->isAutoloaded()) {
            $this->classLoader->autoload();
        }

        if ($container->has('request_stack')) {
            $request = $container->get('request_stack')->getCurrentRequest();
        } else {
            $request = $container->get('request');
        }

        $dispatcher = $container->get('event_dispatcher');
        $event = new LegacyKernelBootEvent($request, $this->options);
        $dispatcher->dispatch(LegacyKernelEvents::BOOT, $event);
        $this->options = $event->getOptions();

        require_once $this->rootDir.'/config/ProjectConfiguration.class.php';

        $application = $this->options['application'];
        $environment = $this->options['environment'];
        $debug = $this->options['debug'];

        $this->configuration = \ProjectConfiguration::getApplicationConfiguration(
            $application,
            $environment,
            $debug,
            $this->getRootDir()
        );
        $this->configuration->loadHelpers(array('Url'));

        // Create a context to use with some helpers like Url.
        if (!\sfContext::hasInstance()) {
            $session = $container->get('session');
            if ($session->isStarted()) {
                $session->save();
            }

            ob_start();
            \sfContext::createInstance($this->configuration);
            ob_end_flush();

            $session->migrate();
        }

        $this->isBooted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $session = $request->getSession();
        if ($session->isStarted()) {
            $session->save();
        }

        ob_start();
        $context = \sfContext::getInstance();
        $context->dispatch();
        $context->shutdown();
        ob_end_clean();

        // @todo: the debug toolbar is not displayed
        return $this->convertResponse($context->getResponse());
    }

    /**
     * Check whether the legacy kernel is already booted or not.
     *
     * @return boolean
     */
    public function isBooted()
    {
        return $this->isBooted;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'symfony14';
    }

    /**
     * Convert the symfony 1.4 response to a Response.
     * @param  \sfWebResponse $legacyResponse
     * @return Response
     */
    private function convertResponse($legacyResponse)
    {
        $response = new Response($legacyResponse->getContent(), $legacyResponse->getStatusCode());
        $response->setCharset($legacyResponse->getCharset());
        $response->setStatusCode($legacyResponse->getStatusCode());

        foreach($legacyResponse->getHttpHeaders() as $headerName => $headerValue) {
            $response->headers->set($headerName, $headerValue);
        }

        return $response;
    }
}
