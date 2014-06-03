<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface;

/**
 * Symfony14 kernel handles.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony14 implements HttpKernelInterface
{
    /**
     * @var string
     */
    private $legacyPath;

    /**
     * @param string $legacyPath
     */
    public function __construct($legacyPath)
    {
        $this->legacyPath = $legacyPath;
        $this->vendorPath = $this->legacyPath.'/lib/vendor';
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        // stop the session to let symfony 1.4 start its own.
        $session = $request->getSession();
        if ($session->isStarted()) {
            $session->save();
        }

        require_once $this->legacyPath.'/config/ProjectConfiguration.class.php';

        // @todo make the app and env parameters dynamic
        $configuration = \ProjectConfiguration::getApplicationConfiguration('ManPerf', 'dev', true);
        $context = \sfContext::createInstance($configuration);
        ob_start();
        $context->dispatch();
        $context->shutdown();
        ob_end_clean();

        return $this->convertResponse($context->getResponse());
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
     * @param $legacyResponse
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
