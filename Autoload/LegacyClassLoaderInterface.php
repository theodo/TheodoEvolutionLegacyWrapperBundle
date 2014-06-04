<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload;

use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface;

/**
 * LegacyClassLoaderInterface
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface LegacyClassLoaderInterface
{
    /**
     * Autoload the legacy code.
     *
     * @return void
     */
    public function autoload();

    /**
     * Check whether the legacy is already autoloaded.
     *
     * @return bool
     */
    public function isAutoloaded();

    /**
     * Inject the kernel into the class looader.
     *
     * @param LegacyKernelInterface $kernel
     */
    public function setKernel(LegacyKernelInterface $kernel);
}
 