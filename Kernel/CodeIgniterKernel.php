<?php

/**
 * The namespace must be defined this way to allow to define the
 * get_instance function later (see the bottom of the file)
 */
namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel {

    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface;

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
         * @var LegacyClassLoaderInterface
         */
        private $classLoader;

        /**
         * @var ContainerInterface
         */
        private $container;

        /**
         * {@inheritdoc}
         */
        public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
        {
            $response = new Response();

            global $CFG, $RTR, $BM, $EXT, $CI, $URI, $OUT;

            ob_start();

            // Set the error handler of CodeIgniter
            set_error_handler('_exception_handler');

            // Load the app controller and local controller
            require_once BASEPATH . 'core/Controller.php';

            if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
                require_once APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
            }

            // Load the local application controller
            // Note: The Router class automatically validates the controller path using the router->_validate_request().
            // If this include fails it means that the default controller in the Routes.php file is not resolving to something valid.
            if (!file_exists(APPPATH . 'controllers/' . $RTR->fetch_directory() . $RTR->fetch_class() . '.php')) {
                throw new CodeIgniterException('Unable to load your default controller. Please make sure the controller specified in your Routes.php file is valid.');
            }

            include(APPPATH . 'controllers/' . $RTR->fetch_directory() . $RTR->fetch_class() . '.php');

            // Set a mark point for benchmarking
            $BM->mark('loading_time:_base_classes_end');

            // Security check
            $class = $RTR->fetch_class();
            $method = $RTR->fetch_method();

            if (!class_exists($class)
                OR strncmp($method, '_', 1) == 0
                OR in_array(strtolower($method), array_map('strtolower', get_class_methods('CI_Controller')))
            ) {
                if (!empty($RTR->routes['404_override'])) {
                    $x = explode('/', $RTR->routes['404_override']);
                    $class = $x[0];
                    $method = (isset($x[1]) ? $x[1] : 'index');
                    if (!class_exists($class)) {
                        if (!file_exists(APPPATH . 'controllers/' . $class . '.php')) {
                            $response->setStatusCode(404);
                            $response->setContent("The {$class}/{$method} does not exist in CodeIgniter.");
                        }

                        include_once(APPPATH . 'controllers/' . $class . '.php');
                    }
                } else {
                    $response->setStatusCode(404);
                    $response->setContent("The {$class}/{$method} does not exist in CodeIgniter.");
                }
            }

            // Is there a "pre_controller" hook?
            $EXT->_call_hook('pre_controller');

            // Instantiate the requested controller
            // Mark a start point so we can benchmark the controller
            $BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_start');

            $CI = new $class();

            // Is there a "post_controller_constructor" hook?
            $EXT->_call_hook('post_controller_constructor');

            // Call the requested method
            // Is there a "remap" function? If so, we call it instead
            if (method_exists($CI, '_remap')) {
                $CI->_remap($method, array_slice($URI->rsegments, 2));
            } else {
                // is_callable() returns TRUE on some versions of PHP 5 for private and protected
                // methods, so we'll use this workaround for consistent behavior
                if (!in_array(strtolower($method), array_map('strtolower', get_class_methods($CI)))) {
                    // Check and see if we are using a 404 override and use it.
                    if (!empty($RTR->routes['404_override'])) {
                        $x = explode('/', $RTR->routes['404_override']);
                        $class = $x[0];
                        $method = (isset($x[1]) ? $x[1] : 'index');
                        if (!class_exists($class)) {
                            if (!file_exists(APPPATH . 'controllers/' . $class . '.php')) {
                                $response->setStatusCode(404);
                                $response->setContent("The {$class}/{$method} does not exist in CodeIgniter.");
                            }

                            include_once(APPPATH . 'controllers/' . $class . '.php');
                            unset($CI);
                            $CI = new $class();
                        }
                    } else {
                        $response->setStatusCode(404);
                        $response->setContent("The {$class}/{$method} does not exist in CodeIgniter.");
                    }
                }

                // Call the requested method.
                // Any URI segments present (besides the class/function) will be passed to the method for convenience
                call_user_func_array(array(&$CI, $method), array_slice($URI->rsegments, 2));
            }

            // Mark a benchmark end point
            $BM->mark('controller_execution_time_( ' . $class . ' / ' . $method . ' )_end');

            // Is there a "post_controller" hook?
            $EXT->_call_hook('post_controller');

            // Send the final rendered output to the browser
            if ($EXT->_call_hook('display_override') === false) {
                $OUT->_display();
            }

            // Is there a "post_system" hook?
            $EXT->_call_hook('post_system');

            // Close the DB connection if one exists
            if (class_exists('CI_DB') AND isset($CI->db)) {
                $CI->db->close();
            }

            ob_end_clean();

            // Restore the Symfony2 error handler
            restore_error_handler();

            if (404 !== $response->getStatusCode()) {
                $response->setContent($OUT->get_output());
            }

            return $response;
        }

        /**
         * {@inheritdoc}
         */
        public function boot(ContainerInterface $container)
        {
            if (empty($this->options)) {
                throw new \RuntimeException('You must provide options for the CodeIgniter kernel.');
            }

            $this->container = $container;

            // Defines constants as it is done in the index.php file of your CodeIgniter project.
            define('ENVIRONMENT', $this->options['environment']);
            define('CI_VERSION', $this->options['version']);
            define('CI_CORE', $this->options['core']);

            // The name of the front controller
            define('SELF', trim($container->get('request_stack')->getCurrentRequest()->getBaseUrl(), '/'));

            // The PHP file extension
            // this global constant is deprecated but used in some third party modules of CodeIgniter
            define('EXT', '.php');

            // Path to the system folder
            define('BASEPATH', str_replace("\\", "/", realpath($this->getRootDir() . '/system/') . '/'));

            // Path to the front controller (this file)
            define('FCPATH', $container->getParameter('kernel.root_dir') . '/../web');

            // Name of the "system folder"
            define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

            // The path to the "application" folder
            define('APPPATH', $this->getRootDir() . '/application/');

            // The path to the "sparks" folder
            define('SPARKPATH', $this->getRootDir() . '/sparks/');


            if (empty($this->classLoader)) {
                throw new \RuntimeException('You must provide a class loader to the CodeIgniter kernel.');
            }

            if (!$this->classLoader->isAutoloaded()) {
                $this->classLoader->autoload();
            }

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
         * {@inheritdoc}
         */
        public function setClassLoader(LegacyClassLoaderInterface $classLoader)
        {
            $classLoader->setKernel($this);
            $this->classLoader = $classLoader;
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

        /**
         * @return \Symfony\Component\DependencyInjection\ContainerInterface
         */
        public function getContainer()
        {
            return $this->container;
        }
    }
}

namespace {
    function &get_instance()
    {
        return \CI_Controller::get_instance();
    }
}