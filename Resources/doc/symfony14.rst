Wrapp symfony >=1.4
===================

Here is the basic configuration for a symfony >=1.4 application:
::

    theodo_evolution_legacy_wrapper:
        root_dir: %kernel.root_dir%/../legacy
        kernel:
            id: theodo_evolution_legacy_wrapper.legacy_kernel.symfony14
            options:
                application: frontend
                environment: '%kernel.environment%'
                debug:       '%kernel.debug%
