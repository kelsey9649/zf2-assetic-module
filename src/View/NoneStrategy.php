<?php

namespace AsseticBundle\View;

use Assetic\Asset\AssetInterface;

/**
 * Class NoneStrategy
 * @package AsseticBundle\View
 */
class NoneStrategy extends AbstractStrategy
{
    /**
     * @param AssetInterface $asset
     */
    public function setupAsset(AssetInterface $asset)
    {
    }
}
