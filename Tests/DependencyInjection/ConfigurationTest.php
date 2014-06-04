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
        $configs = array(
            array(
                'root_dir'  => '/foo/bar',
                'kernel_id' => 'foo'
            )
        );

        $config = $this->process($configs);

        $this->assertEquals('/foo/bar', $config['root_dir']);
        $this->assertEquals('foo', $config['kernel_id']);
        $this->assertArrayNotHasKey('class_loader_id', $config);
        $this->assertEmpty($config['assets']);
    }

    public function testProcessBasicAssetsConfiguration()
    {
        $configs = array(
            array(
                'root_dir'  => '/foo/bar',
                'kernel_id' => 'foo',
                'assets'    => array(
                    'web' => array(
                        'base' => '/web',
                        'directories' => array('css', 'js')
                    )
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
        $configs = array(
            array(
                'root_dir'  => '/foo/bar',
                'kernel_id' => 'foo',
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
}
 