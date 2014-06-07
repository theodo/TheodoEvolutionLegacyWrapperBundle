<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface;

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

        if (empty($this->classLoader)) {
            throw new \RuntimeException('You must provide a class loader to the Symfony 1.4 kernel.');
        }

        if (!$this->classLoader->isAutoloaded()) {
            $this->classLoader->autoload();
        }

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

        $context = \sfContext::createInstance($this->configuration);// be careful this will start the session of symfony 1
        ob_start();
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
        $response->headers->set('Content-Type', $legacyResponse->getContentType());

        return $response;
    }
}
