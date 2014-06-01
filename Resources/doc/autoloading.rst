Autoloading
===========

To autoload your legacy project you will need to create a ``LegacyClassLoader`` that will call
its autoloading mechanism only when the RouterListener will call the legacy kernel
to handle the request. This way, the legacy project will never been autoloaded if
not required.

To create your own legacy class loader you must create a class that implements the
the ``LegacyClassLoaderInterface`` which defines the ``autoload`` method. This is where
you would implement the legacy autoloading stuff. This method will be called by the
``RouterListener`` before handling the request as the legacy project would do.

You can also implement the ``ComposerLoaderAwareInterface`` which allows you to inject the
autoloader provided by Composer. This methods will do more or less the same thing as the
generated ``autoload_real.php`` file does.

**NOTE:** if you want to use the autoloader of Composer your ``AppKernel`` class  must
extend the ``EvolutionKernel``, see `the installation documentation`_.

.. _the installation documentation: ../../README.md


Example
=======

Here is an example of a legacy class loader for `symfony 1.5 <https://github.com/LExpress/symfony1>`:

::

    namespace Acme\Bundle\LegacyBundle\Autoload;
    
    use Composer\Autoload\ClassLoader;
    use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\ComposerLoaderAwareInterface;
    use Theodo\Evolution\Bundle\LegacyWrapperBundle\Autoload\LegacyClassLoaderInterface;
    
    /**
     * AcmeLegacyClassLoader autoloads the symfony >=1.4 framework when it uses Composer as
     * dependency manager.
     * 
     * @author Benjamin Grandfond <benjaming@theodo.fr>
     */
    class AcmeLegacyClassLoader implements ComposerLoaderAwareInterface, LegacyClassLoaderInterface
    {
        /**
         * @var string
         */
        private $legacyPath;
    
        /**
         * @var ClassLoader
         */
        private $loader;
    
        /**
         * @param string $legacyPath The path to the legacy project
         */
        public function __construct($legacyPath)
        {
            $this->legacyPath = $legacyPath;
        }
    
        /**
         * @param ClassLoader $loader
         * @return mixed
         */
        public function setLoader(ClassLoader $loader)
        {
            $this->loader = $loader;
        }
    
        /**
         * {@inheritdoc}
         */
        public function autoload()
        {
            $composerDir = realpath($this->legacyPath.'/lib/vendor/composer');
    
            $map = require $composerDir . '/autoload_namespaces.php';
            $prefixes = $this->loader->getPrefixes();
            foreach ($map as $namespace => $path) {
                if (!array_key_exists($namespace, $prefixes)) {
                    $this->loader->set($namespace, $path);
                }
            }
    
            $classMap = require $composerDir . '/autoload_classmap.php';
            if ($classMap) {
                $this->loader->addClassMap($classMap);
            }
    
            $includeFiles = require $composerDir . '/autoload_files.php';
            foreach ($includeFiles as $file) {
                if (false === strpos($file, 'swiftmailer')) {
                    require $file;
                }
            }
        }
    }

And the service configuration:

::

    acme_legacy.autoload.legacy_class_loader:
        class: %acme_legacy.autoload.legacy_class_loader.class%
        arguments:
            - %theodo_evolution_legacy_wrapper.legacy_path%
        tags:
            - loader_aware


This example uses the generated files by Composer in the legacy project to load namespaced classes,
"class mapped" classes and require files that autoload libraries (i.e. Swift Mailer). In this
example it does not require Swift Mailer autoloading file because Symfony 2 also comes with it by
default. Thus it would generate a "PHP Fatal error: Cannot redeclare class Swift".