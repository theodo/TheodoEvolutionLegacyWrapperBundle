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
}
 