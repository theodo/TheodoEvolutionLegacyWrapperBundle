Wrap CodeIgniter
=================

Here is the basic configuration for a CodeIgniter application:
::

    theodo_evolution_legacy_wrapper:
        root_dir: %kernel.root_dir%/../legacy
        class_loader_id: theodo_evolution_legacy_wrapper.autoload.codeigniter_class_loader
        kernel:
            id: theodo_evolution_legacy_wrapper.legacy_kernel.codeigniter
            options:
                environment: %kernel.environment%
                version: '2.1.2'
                core: false

Slightly advanced configuration for CodeIgniter application:
* where the system folder and application folder reside in locations other than the `%root_dir%/system` and
`%root_dir%/application paths`. If these are not set, they default to looking under the `%root_dir%` for the
appropriate folders

::

    theodo_evolution_legacy_wrapper:
        root_dir: %kernel.root_dir%/../legacy
        class_loader_id: theodo_evolution_legacy_wrapper.autoload.codeigniter_class_loader
        kernel:
            id: theodo_evolution_legacy_wrapper.legacy_kernel.codeigniter
            options:
                environment: %kernel.environment%
                version: '2.1.2'
                core: false
                system: %kernel.root_dir%/../library/CodeIgniter_2.1.4/system
                application: %kernel.root_dir%/../application