<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * The LegacyKernel allows the legacy project to boot and handle the HTTP request.
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface LegacyKernelInterface extends HttpKernelInterface
{
    /**
     * Boot the legacy kernel.
     *
     * @throws \RuntimeException
     * @param  ContainerInterface $container
     * @return mixed
     */
    public function boot(ContainerInterface $container);

    /**
     * Check whether the legacy kernel is already booted or not.
     *
     * @return boolean
     */
    public function isBooted();

    /**
     * Return the directory where the legacy app lives.
     *
     * @return string
     */
    public function getRootDir();

    /**
     * Set the directory where the legacy app lives.
     *
     * @param string $rootDir
     */
    public function setRootDir($rootDir);

    /**
     * Return the name of the kernel.
     *
     * @return string
     */
    public function getName();

    /**
     * Set kernel options
     *
     * @param array $options
     */
    public function setOptions(array $options = array());
}
 