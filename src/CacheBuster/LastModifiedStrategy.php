<?php

namespace AsseticBundle\CacheBuster;

use Assetic\Asset\AssetInterface,
    Assetic\Factory\Worker\WorkerInterface,
    Assetic\Factory\AssetFactory;

/**
 * Class LastModifiedStrategy
 * @package AsseticBundle\CacheBuster
 */
class LastModifiedStrategy implements WorkerInterface
{
    /**
     * @param AssetInterface $asset
     * @param AssetFactory $factory
     * @return AssetInterface|null|void
     */
    public function process(AssetInterface $asset, AssetFactory $factory)
    {
        $path = $asset->getTargetPath();
        $extension  = pathinfo($path, PATHINFO_EXTENSION);

        $lastModified = $factory->getLastModified($asset);

        if (null !== $lastModified) {

            $path = substr_replace(
                $path,
                "$lastModified.$extension",
                -1 * strlen($extension)
            );

            $asset->setTargetPath($path);
        }
    }
}
