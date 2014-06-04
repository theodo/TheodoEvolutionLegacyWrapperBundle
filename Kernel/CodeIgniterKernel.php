<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CodeIgniterKernel implements LegacyKernelInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var boolean
     */
    private $isBooted = false;

    /**
     * @var array
     */
    private $options = array();

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        ob_start();
        require_once $this->getRootDir().'/index.php';
        $content  = ob_get_contents();
        ob_end_flush();

        return new Response($content);

    }

    /**
     * Boot the legacy kernel.
     *
     * @param  ContainerInterface $container
     * @return mixed
     */
    public function boot(ContainerInterface $container)
    {
        if (empty($this->options)) {
            throw new \RuntimeException('You must provide options for the CodeIgniter kernel.');
        }

        define('ENVIRONMENT', $this->options['environment']);

        $system_path = realpath($this->getRootDir().'/system/');
        $application_folder = $this->getRootDir().'/application';

        // The name of THIS file
        define('SELF', 'index.php');

        // Path to the system folder
        define('BASEPATH', str_replace("\\", "/", $system_path));

        // Path to the front controller (this file)
        define('FCPATH', $this->getRootDir());

        // Name of the "system folder"
        define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

        // The path to the "application" folder
        define('APPPATH', $application_folder.'/');

        $this->isBooted = true;
    }

    /**
     * Check whether the legacy kernel is already booted or not.
     *
     * @return boolean
     */
    public function isBooted()
    {
        return $this->isBooted;
    }

    /**
     * Return the directory where the legacy app lives.
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * Set the directory where the legacy app lives.
     *
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Return the name of the kernel.
     *
     * @return string
     */
    public function getName()
    {
        return 'codeigniter';
    }

    /**
     * Set kernel options
     *
     * @param array $options
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    }
} 