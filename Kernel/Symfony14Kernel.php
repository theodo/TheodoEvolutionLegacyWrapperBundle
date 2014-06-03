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
class Symfony14Kernel implements LegacyKernelInterface
{
    /**
     * @var string
     */
    private $legacyPath;

    /**
     * @var LegacyClassLoaderInterface
     */
    private $classLoader;

    /**
     * @var boolean
     */
    private $isBooted;

    /**
     * @var \sfApplicationConfiguration
     */
    private $configuration;

    /**
     * @param $legacyPath
     * @param LegacyClassLoaderInterface $classLoader
     */
    public function __construct($legacyPath, LegacyClassLoaderInterface $classLoader)
    {
        $this->legacyPath  = $legacyPath;
        $this->vendorPath  = $this->legacyPath.'/lib/vendor';
        $this->classLoader = $classLoader;
        $this->isBooted    = false;
    }

    /**
     * {@inheritdoc}
     */
    public function boot(ContainerInterface $container)
    {
        if ($this->isBooted()) {
            return;
        }

        if (!$this->classLoader->isAutoloaded()) {
            $this->classLoader->autoload();
        }

        require_once $this->legacyPath.'/config/ProjectConfiguration.class.php';

        // @todo make the app and env parameters dynamic
        $this->configuration = \ProjectConfiguration::getApplicationConfiguration('ManPerf', 'dev', true);

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

        return $response;
    }
}
