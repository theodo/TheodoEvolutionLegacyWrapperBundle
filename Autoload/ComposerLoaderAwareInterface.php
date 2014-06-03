<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload;

use Composer\Autoload\ClassLoader;

/**
 * ComposerLoaderAwareInterface
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface ComposerLoaderAwareInterface
{
    /**
     * @param ClassLoader $loader
     * @return mixed
     */
    public function setLoader(ClassLoader $loader);
}
 