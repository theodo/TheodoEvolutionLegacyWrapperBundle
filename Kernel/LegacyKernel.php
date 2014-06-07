<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel;

use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface;

/**
 * LegacyKernel
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
abstract class LegacyKernel implements LegacyKernelInterface
{
    /**
     * @var bool
     */
    protected $isBooted = false;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var LegacyClassLoaderInterface
     */
    protected $classLoader;

    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function isBooted()
    {
        return $this->isBooted;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function setClassLoader(LegacyClassLoaderInterface $classLoader)
    {
        $classLoader->setKernel($this);
        $this->classLoader = $classLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    }
}
 