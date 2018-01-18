<?php

namespace AsseticBundle\View;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use Zend\View\Helper\HeadLink;
use Zend\View\Helper\HeadScript;
use Zend\View\Helper\InlineScript;
use Zend\View\Renderer\PhpRenderer;

/**
 * Class ViewHelperStrategy
 * @package AsseticBundle\View
 */
class ViewHelperStrategy extends AbstractStrategy
{
    /**
     * @param AssetInterface $asset
     */
    public function setupAsset(AssetInterface $asset)
    {
        if ($this->isDebug() && !$this->isCombine() && $asset instanceof AssetCollection) {

            // Move assets as single instance not as a collection
            foreach ($asset as $value) {

                /** @var AssetCollection $value */
                $path = $this->getBaseUrl() . $this->getBasePath() . $value->getTargetPath();
                $this->helper($path);
            }

            return;
        }

        $path = $this->getBaseUrl() . $this->getBasePath() . $asset->getTargetPath();
        $this->helper($path);
    }

    /**
     * @param string $filePath
     */
    protected function helper($filePath)
    {
        $extension = pathinfo(
            $filePath,
            PATHINFO_EXTENSION
        );

        $extension = strtolower($extension);

        switch ($extension) {
            case 'js':
                $this->appendScript($filePath);
                break;
            case 'css':
                $this->appendStylesheet($filePath);
                break;
            default:
                break;
        }
    }

    /**
     * @param $filePath
     */
    protected function appendScript($filePath)
    {
        $renderer = $this->getRenderer();

        if (!$renderer instanceof PhpRenderer) {
            return;
        }

        if (strpos($filePath, "head_") !== false) {

            /** @var HeadScript $headScript */
            $headScript = $renderer->plugin('HeadScript');
            $headScript->appendFile($filePath);
            return;
        }

        /** @var InlineScript $inlineScript */
        $inlineScript = $renderer->plugin('InlineScript');
        $inlineScript->appendFile($filePath);
    }

    /**
     * @param string $filePath
     */
    protected function appendStylesheet($filePath)
    {
        $renderer = $this->getRenderer();

        if (!$renderer instanceof PhpRenderer) {
            return;
        }

        /** @var HeadLink $headLink */
        $headLink = $renderer->plugin('HeadLink');
        $headLink->appendStylesheet($filePath);
    }
}
