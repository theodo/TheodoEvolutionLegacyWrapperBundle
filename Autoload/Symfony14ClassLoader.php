<?php
namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload;

use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface;

class Symfony14ClassLoader implements LegacyClassLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function autoload()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAutoloaded()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(LegacyKernelInterface $kernel)
    {
    }
}