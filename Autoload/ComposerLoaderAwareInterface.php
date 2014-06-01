<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload;

use Composer\Autoload\ClassLoader;

/**
 * ComposerLoaderAwareInterface
 * 
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface ComposerLoaderAwareInterface
{
    /**
     * @param ClassLoader $loader
     * @return mixed
     */
    public function setLoader(ClassLoader $loader);
}
 