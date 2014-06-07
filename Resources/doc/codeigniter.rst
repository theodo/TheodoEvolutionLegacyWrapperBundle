Wrapp CodeIgniter
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

