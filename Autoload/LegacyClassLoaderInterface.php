<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload;

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
}
 