<?php

namespace AsseticBundle\CacheBuster;

use Assetic\Asset\AssetInterface,
    Assetic\Factory\Worker\WorkerInterface,
    Assetic\Factory\AssetFactory;

/**
 * Class Null
 * @package AsseticBundle\CacheBuster
 */
class Null implements WorkerInterface
{
    /**
     * @param AssetInterface $asset
     * @param AssetFactory $factory
     * @return AssetInterface|null|void
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
    }
}
