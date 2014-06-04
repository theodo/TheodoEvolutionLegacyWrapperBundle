<?php
/**
 * Created by PhpStorm.
 * User: benjamin
 * Date: 04/06/2014
 * Time: 22:45
 */

namespace DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Theodo\Evolution\Bundle\LegacyWrapperBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessBasicConfiguration()
    {
        $configs = $this->getBasicConfiguration();

        $config = $this->process($configs);

        $this->assertEquals('/foo/bar', $config['root_dir']);
        $this->assertEquals('foo', $config['kernel']['id']);
        $this->assertArrayNotHasKey('class_loader_id', $config);
        $this->assertEmpty($config['assets']);
    }

    public function testProcessSymfony14KernelConfiguration()
    {
        $configs = array(
            array(
                'root_dir' => '/foo',
                'kernel'   => array(
                    'id' => 'legacy_kernel.symfony14',
                    'options' => array(
                        'application' => 'bar',
                        'environment' => '%kernel.environment%',
                        'debug'       => '%kernel.debug%',
                    )
                )
            )
        );

        $config = $this->process($configs);

        $this->assertEquals('legacy_kernel.symfony14', $config['kernel']['id']);
        $this->assertArrayHasKey('application', $config['kernel']['options']);
        $this->assertArrayHasKey('environment', $config['kernel']['options']);
        $this->assertArrayHasKey('debug', $config['kernel']['options']);
    }

    public function testProcessBasicAssetsConfiguration()
    {
        $configs   = $this->getBasicConfiguration();
        $configs[0] += array(
            'assets'    => array(
                'web' => array(
                    'base' => '/web',
                    'directories' => array('css', 'js')
                )
            )
        );

        $config = $this->process($configs);

        $this->assertArrayHasKey('web', $config['assets']);
        $this->assertEquals('/web', $config['assets']['web']['base']);
        $this->assertCount(2, $config['assets']['web']['directories']);
    }

    public function testProcessMultipleAssetsConfiguration()
    {
        $configs   = $this->getBasicConfiguration();
        $configs[0] += array(
            'assets'    => array(
                'foo' => array(
                    'base' => '/foo',
                    'directories' => array('css', 'js')
                ),
                'bar' => array(
                    'base' => '/bar',
                    'directories' => array('css', 'js')
                )
            )
        );

        $config = $this->process($configs);

        $this->assertCount(2, $config['assets']);
    }

    /**
     * Processes an array of configurations and returns a compiled version.
     *
     * @param array $configs An array of raw configurations
     *
     * @return array A normalized array
     */
    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    /**
     * @return array
     */
    private function getBasicConfiguration()
    {
        return array(
            array(
                'root_dir' => '/foo/bar',
                'kernel' => 'foo'
            )
        );
    }
}
 