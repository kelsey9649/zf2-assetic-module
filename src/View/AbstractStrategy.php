<?php

namespace AsseticBundle\View;

use Zend\View\Renderer\RendererInterface;

/**
 * Class AbstractStrategy
 * @package AsseticBundle\View
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /** @var RendererInterface */
    protected $renderer;

    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $basePath;

    /** @var bool  */
    protected $debug = false;

    /** @var bool  */
    protected $combine = true;

    /**
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return \Zend\View\Renderer\RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param bool $flag
     */
    public function setDebug($flag)
    {
        $this->debug = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param bool $flag
     */
    public function setCombine($flag)
    {
        $this->combine = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function isCombine()
    {
        return $this->combine;
    }
}
