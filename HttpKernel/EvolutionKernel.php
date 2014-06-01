<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\HttpKernel;

use Composer\Autoload\ClassLoader;
use Symfony\Component\HttpKernel\Kernel;

/**
 * EvolutionKernel
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
abstract class EvolutionKernel extends Kernel
{
    /**
     * @var ClassLoader
     */
    protected $loader;

    /**
     * Overrides the parent method to add the loader to the container.
     *
     * {@inheritdoc}
     */
    protected function initializeContainer()
    {
        parent::initializeContainer();

        $this->container->set('composer.loader', $this->loader);
    }

    /**
     * @param ClassLoader $loader
     */
    public function setLoader(ClassLoader $loader)
    {
        $this->loader = $loader;
    }
}
 