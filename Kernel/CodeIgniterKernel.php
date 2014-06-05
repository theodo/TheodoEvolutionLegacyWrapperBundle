<?php

/**
 * The namespace must be defined this way to allow to define the
 * get_instance function later (see the bottom of the file)
 */
namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel {

    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

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
         * {@inheritdoc}
         */
        public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
        {
            global $CFG, $RTR, $BM, $EXT, $CI, $URI, $OUT;

            ob_start();
            // Load the app controller and local controller
            require_once BASEPATH . 'core/Controller.php';

            if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
                require_once APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
            }

            // Load the local application controller
            // Note: The Router class automatically validates the controller path using the router->_validate_request().
            // If this include fails it means that the default controller in the Routes.php file is not resolving to something valid.
            if (!file_exists(APPPATH . 'controllers/' . $RTR->fetch_directory() . $RTR->fetch_class() . '.php')) {
                show_error(
                    'Unable to load your default controller. Please make sure the controller specified in your Routes.php file is valid.'
                );
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
                            show_404("{$class}/{$method}");
                        }

                        include_once(APPPATH . 'controllers/' . $class . '.php');
                    }
                } else {
                    show_404("{$class}/{$method}");
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
                                show_404("{$class}/{$method}");
                            }

                            include_once(APPPATH . 'controllers/' . $class . '.php');
                            unset($CI);
                            $CI = new $class();
                        }
                    } else {
                        show_404("{$class}/{$method}");
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

            $content = ob_get_contents();
            ob_end_flush();

            // Restore the Symfony2 error handler
            restore_error_handler();

            return new Response($content);

        }

        /**
         * {@inheritdoc}
         */
        public function boot(ContainerInterface $container)
        {
            if (empty($this->options)) {
                throw new \RuntimeException('You must provide options for the CodeIgniter kernel.');
            }

            // Defines constants as it is done in the index.php file of your CodeIgniter project.
            define('ENVIRONMENT', $this->options['environment']);

            // The name of the front controller
            define('SELF', 'app.php');

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

            define('SPARKPATH', $this->getRootDir() . '/sparks/');

            $this->initialize($container);

            $this->isBooted = true;
        }

        /**
         * This methods initialize CodeIngiter. It has been copied/pasted
         * and tweaked from a core/CodeIgniter file from CodeIgniter 2.1.2.
         *
         * @param ContainerInterface $container
         */
        private function initialize(ContainerInterface $container)
        {
            //@todo what to do with the CI version and core vars ?
            define('CI_VERSION', '2.1.2');
            define('CI_CORE', false);

            // Declare CodeIgniter global variables
            //, $EXT, $CFG, $UNI, $URI, $RTR, $OUT, $SEC, $IN, $LANG;

            // Load the global functions
            require_once(BASEPATH . 'core/Common.php');

            // Load the framework constants
            if (defined('ENVIRONMENT') AND file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
                require_once(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
            } else {
                require_once(APPPATH . 'config/constants.php');
            }

            // @todo: move this in the handle method
            set_error_handler('_exception_handler');

            // Set the subclass_prefix
            if (isset($assign_to_config['subclass_prefix']) AND $assign_to_config['subclass_prefix'] != '') {
                get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
            }

            // Set a liberal script execution time limit
            if (function_exists("set_time_limit") == true AND @ini_get("safe_mode") == 0) {
                @set_time_limit(300);
            }

            // Start the timer... tick tock tick tock...
            $BM =& load_class('Benchmark', 'core');
            $BM->mark('total_execution_time_start');
            $BM->mark('loading_time:_base_classes_start');
            $GLOBALS['BM'] = $BM;
            $container->set('BM', $BM);

            // Instantiate the hooks class
            $EXT =& load_class('Hooks', 'core');
            $GLOBALS['EXT'] = $EXT;
            $container->set('EXT', $EXT);

            // Is there a "pre_system" hook?
            $EXT->_call_hook('pre_system');

            // Instantiate the config class
            $CFG =& load_class('Config', 'core');
            $GLOBALS['CFG'] = $CFG;
            $container->set('CFG', $CFG);

            // Do we have any manually set config items in the index.php file?
            if (isset($assign_to_config)) {
                $CFG->_assign_to_config($assign_to_config);
            }

            // Instantiate the UTF-8 class
            $UNI =& load_class('Utf8', 'core');
            $GLOBALS['UNI'] = $UNI;
            $container->set('UNI', $UNI);

            // Instantiate the URI class
            $URI =& load_class('URI', 'core');
            $GLOBALS['URI'] = $URI;
            $container->set('URI', $URI);

            // Instantiate the routing class and set the routing
            $RTR =& load_class('Router', 'core');
            $RTR->_set_routing();
            $GLOBALS['RTR'] =$RTR;
            $container->set('RTR', $RTR);

            // Set any routing overrides that may exist in the main index file
            if (isset($routing)) {
                $RTR->_set_overrides($routing);
            }

            // Instantiate the output class
            $OUT =& load_class('Output', 'core');
            $GLOBALS['OUT'] = $OUT;
            $container->set('OUT', $OUT);

            // Is there a valid cache file?  If so, we're done...
            if ($EXT->_call_hook('cache_override') === false) {
                // @todo: move this somewhere else ?
                if ($OUT->_display_cache($CFG, $URI) == true) {
                    exit;
                }
            }

            // Load the security class for xss and csrf support
            $SEC =& load_class('Security', 'core');
            $GLOBALS['SEC'] = $SEC;
            $container->set('SEC', $SEC);

            // Load the Input class and sanitize globals
            $IN =& load_class('Input', 'core');
            $GLOBALS['IN'] = $IN;
            $container->set('IN', $IN);

            // Load the Language class
            $LANG =& load_class('Lang', 'core');
            $GLOBALS['LANG'] = $LANG;
            $container->set('LANG', $LANG);
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
}

namespace {
    function &get_instance()
    {
        return \CI_Controller::get_instance();
    }
}