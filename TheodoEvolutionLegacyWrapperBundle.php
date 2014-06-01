<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle;

use Composer\Autoload\ClassLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection\Compiler\LoaderInjectorPass;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection\Compiler\ReplaceRouterPass;

class TheodoEvolutionLegacyWrapperBundle extends Bundle
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private $loader;

    /**
     * @param ClassLoader $loader
     */
    public function __construct(ClassLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LoaderInjectorPass($this->loader));
        $container->addCompilerPass(new ReplaceRouterPass());
    }
}
