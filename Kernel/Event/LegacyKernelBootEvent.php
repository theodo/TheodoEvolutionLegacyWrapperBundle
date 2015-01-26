<?php

namespace Theodo\Evolution\Bundle\LegacyWrapperBundle\Kernel\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class LegacyKernelBootEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array - legacy kernel options
     */
    protected $options;

    /**
     * @param Request $request
     * @param array $options
     */
    public function __construct(Request $request, array $options = array())
    {
        $this->request = $request;
        $this->options = $options;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

}
