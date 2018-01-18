<?php

namespace AsseticBundle\View;

use Zend\View\Renderer\RendererInterface,
    Assetic\Asset\AssetInterface;

/**
 * Interface StrategyInterface
 * @package AsseticBundle\View
 */
interface StrategyInterface
{
    public function setRenderer(RendererInterface $renderer);
    public function getRenderer();

    public function setBaseUrl($baseUrl);
    public function getBaseUrl();

    public function setBasePath($basePath);
    public function getBasePath();

    public function setDebug($flag);
    public function isDebug();

    public function setCombine($flag);
    public function isCombine();

    public function setupAsset(AssetInterface $asset);
}
