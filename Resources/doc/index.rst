Installation
============

This bundle requires Symfony 2.4 or higher.

Add the following lines to your composer.json:

::

    "require": {
        ...
        "theodo-evolution/legacy-wrapper-bundle": "~1.0"
        ...
    },

And run Composer:

::

    php composer.phar update theodo-evolution/legacy-wrapper-bundle

Configuration
=============

* Add the bundles in your app/AppKernel.php:

::

    public function registerBundles()
    {
        $bundles = array(
            //vendors, other bundles...
            new Theodo\Evolution\Bundle\LegacyWrapperBundle\TheodoEvolutionLegacyWrapperBundle(),
        );
    }

* Configure the bundle in your app/config/config.yml:

::

    theodo_evolution_legacy_wrapper:
        legacy_path:     %kernel.root_dir%/../legacy
        kernel_id:       theodo_evolution_legacy_wrapper.legacy_kernel.symfony14
        class_loader_id: your_bundle.autoload.legacy_class_loader

What these options are?

 * ``legacy_path``: this tells the legacy kernel where your legacy project lives
 * ``kernel_id``: you can specify which legacy kernel you want to use. Using an id allows
                  you to use your own kernel or use those provided by the bundle (see
                  [the list](../../Kernel)
 * ``class_loader_id``: specify the service that will handle the autoloading of your legacy project.
                        The bundle does not provide (yet) any autoloader, you have to [create your own](autoloading.rst).

How it works
============

The aim of the bundle is to allow a legacy project to handle HTTP requests instead of Symfony 2,
in a simple way:
 * let Symfony try to find a controller that match the request
 * if it can't let the legacy project try to do so
 * if it can't either, return a 404 response

To do so, the LegacyWrapperBundle provides a ```RouterLister``` which will delegates the request
handling to the ```RouterListener``` of Symfony 2 and if a ```HttpNotFoundException``` is thrown
it will call the legacy kernel, which is an implementation of the ``HttpKernelInterface`` of the
legacy project.