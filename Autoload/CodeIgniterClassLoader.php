<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload;

use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\CodeIgniterKernel;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\LegacyKernelInterface;

/**
 * CodeIgniterClassLoader
 * 
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class CodeIgniterClassLoader implements LegacyClassLoaderInterface
{
    /**
     * @var boolean
     */
    private $isAutoloaded = false;

    /**
     * @var LegacyKernelInterface
     */
    private $kernel;

    /**
     * Autoload the legacy code.
     *
     * @return void
     */
    public function autoload()
    {
        // Load the global functions
        require_once(BASEPATH . 'core/Common.php');

        // Load the framework constants
        if (defined('ENVIRONMENT') AND file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
            require_once(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
        } else {
            require_once(APPPATH . 'config/constants.php');
        }

        // Set the subclass_prefix
        if (isset($assign_to_config['subclass_prefix']) AND $assign_to_config['subclass_prefix'] != '') {
            get_config(array('subclass_prefix' => $assign_to_config['subclass_prefix']));
        }

        // Set a liberal script execution time limit
        if (function_exists("set_time_limit") == true AND @ini_get("safe_mode") == 0) {
            @set_time_limit(300);
        }

        // Start the timer... tick tock tick tock...
        $BM = load_class('Benchmark', 'core');
        $BM->mark('total_execution_time_start');
        $BM->mark('loading_time:_base_classes_start');
        $GLOBALS['BM'] = $BM;
        $this->kernel->getContainer()->set('BM', $BM);

        // Instantiate the hooks class
        $EXT = load_class('Hooks', 'core');
        $GLOBALS['EXT'] = $EXT;
        $this->kernel->getContainer()->set('EXT', $EXT);

        // Is there a "pre_system" hook?
        $EXT->_call_hook('pre_system');

        // Instantiate the config class
        $CFG = load_class('Config', 'core');
        $GLOBALS['CFG'] = $CFG;
        $this->kernel->getContainer()->set('CFG', $CFG);

        // Do we have any manually set config items in the index.php file?
        if (isset($assign_to_config)) {
            $CFG->_assign_to_config($assign_to_config);
        }

        // Instantiate the UTF-8 class
        $UNI = load_class('Utf8', 'core');
        $GLOBALS['UNI'] = $UNI;
        $this->kernel->getContainer()->set('UNI', $UNI);

        // Instantiate the URI class
        $URI = load_class('URI', 'core');
        $GLOBALS['URI'] = $URI;
        $this->kernel->getContainer()->set('URI', $URI);

        // Instantiate the routing class and set the routing
        $RTR = load_class('Router', 'core');
        $RTR->_set_routing();
        $GLOBALS['RTR'] =$RTR;
        $this->kernel->getContainer()->set('RTR', $RTR);

        // Set any routing overrides that may exist in the main index file
        if (isset($routing)) {
            $RTR->_set_overrides($routing);
        }

        // Instantiate the output class
        $OUT = load_class('Output', 'core');
        $GLOBALS['OUT'] = $OUT;
        $this->kernel->getContainer()->set('OUT', $OUT);

        // Is there a valid cache file?  If so, we're done...
        if ($EXT->_call_hook('cache_override') === false) {
            if ($OUT->_display_cache($CFG, $URI) == true) {
                exit;
            }
        }

        // Load the security class for xss and csrf support
        $SEC = load_class('Security', 'core');
        $GLOBALS['SEC'] = $SEC;
        $this->kernel->getContainer()->set('SEC', $SEC);

        // Load the Input class and sanitize globals
        $IN = load_class('Input', 'core');
        $GLOBALS['IN'] = $IN;
        $this->kernel->getContainer()->set('IN', $IN);

        // Load the Language class
        $LANG = load_class('Lang', 'core');
        $GLOBALS['LANG'] = $LANG;
        $this->kernel->getContainer()->set('LANG', $LANG);

        // Load the app controller and local controller
        require_once BASEPATH . 'core/Controller.php';

        $this->isAutoloaded = true;
    }

    /**
     * Check whether the legacy is already autoloaded.
     *
     * @return bool
     */
    public function isAutoloaded()
    {
        return $this->isAutoloaded;
    }

    /**
     * {@inheritdoc}
     * 
     * @throws \InvalidArgumentException
     */
    public function setKernel(LegacyKernelInterface $kernel)
    {
        if (!$kernel instanceof CodeIgniterKernel) {
            throw new \InvalidArgumentException(sprintf('The "%s" expects a CodeIgniterKernel, "%s" given', __CLASS__, get_class($kernel)));
        }

        $this->kernel = $kernel;
    }

}
 