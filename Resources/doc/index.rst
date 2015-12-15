How it works
============

The aim of the bundle is to allow a legacy project to handle HTTP requests instead of Symfony 2,
in a simple way:

* let Symfony try to find a controller that matches the request
* if it can't, it let the legacy project try to return a response
* otherwise return a 404 response

To do so, the LegacyWrapperBundle provides a ```RouterLister``` which will delegates the request
handling to the ```RouterListener``` of Symfony 2 and if a ```HttpNotFoundException``` is thrown
it will call the legacy kernel, which is an implementation of the ``HttpKernelInterface`` of the
legacy project.

Installation
============

This bundle requires Symfony 2.4 or higher.

Add the following lines to your composer.json:

::

    "require": {
        ...
        "composer/composer": "~1.0@dev",
        "theodo-evolution/legacy-wrapper-bundle": "~1.0"
        ...
    },

And run Composer:

::

    php composer.phar update theodo-evolution/legacy-wrapper-bundle

Configuration
=============

Add the bundles in your app/AppKernel.php:

::

    public function registerBundles()
    {
        $bundles = array(
            //vendors, other bundles...
            new Theodo\Evolution\Bundle\LegacyWrapperBundle\TheodoEvolutionLegacyWrapperBundle(),
        );
    }

Configure the bundle in your app/config/config.yml:

* `Symfony >=1.4 configuration`_
* `Code Igniter ~2.1 configuration`_
* `Custom kernel configuration`_

.. _Symfony >=1.4 configuration: symfony14.rst
.. _Code Igniter ~2.1 configuration: codeigniter.rst
.. _Custom kernel configuration: customkernel.rst

Autoloading
===========

To make the legacy application handle the request or to use the legacy code from Symfony 2
(in a controller for example) you must autoload it.

See the `autoloading`_ guide.

.. _autoloading: autoloading.rst
