<?php

namespace AsseticBundle\View\Helper;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use AsseticBundle\Exception;
use AsseticBundle\Factory\ServiceFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Placeholder\Container;

/**
 * Class Asset
 *
 * @package AsseticBundle\View\Helper
 */
class Asset extends Container\AbstractStandalone
{
    /** @var \AsseticBundle\Service|null */
    protected $service = null;

    /** @var null|string */
    protected $baseUrl = '';

    /** @var null|string */
    protected $basePath = '';

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        parent::__construct();

        $serviceFactory = new ServiceFactory();
        $this->service = $serviceFactory->createService($serviceLocator);
        $this->service->build();

        $this->baseUrl = $this->service->getConfiguration()->getBaseUrl();
        $this->basePath = $this->service->getConfiguration()->getBasePath();
    }

    /**
     * @param string $collectionName
     * @param array $options
     *
     * @return string
     *
     * @throws \AsseticBundle\Exception\InvalidArgumentException
     */
    public function __invoke($collectionName, array $options = [])
    {
        $assetManager = $this->service->getAssetManager();

        if (!$assetManager->has($collectionName)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Collection "%s" does not exist.',
                    $collectionName
                )
            );
        }

        $asset = $assetManager->get($collectionName);
        return $this->setupAsset($asset, $options);
    }

    /**
     * @param AssetInterface $asset
     * @param array $options
     *
     * @return string
     */
    protected function setupAsset(AssetInterface $asset, array $options = [])
    {
        $configuration = $this->service->getConfiguration();

        if ($configuration->isDebug()
            && !$configuration->isCombine()
            && $asset instanceof AssetCollection
        ) {
            // Move assets as single instance not as a collection
            $response = '';

            foreach ($asset as $value) {
                /** @var AssetCollection $value */
                $response .= $this->helper($value, $options) . PHP_EOL;
            }

            return $response;
        }

        return $this->helper($asset, $options) . PHP_EOL;
    }

    /**
     * @param AssetInterface $asset
     * @param array $options
     *
     * @return string
     */
    protected function helper(AssetInterface $asset, array $options = [])
    {
        $filePath = $this->baseUrl . $this->basePath . $asset->getTargetPath();

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        if (isset($options['addFileMTime']) && $options['addFileMTime']) {
            $filePath .= '?' . $asset->getLastModified();
        }

        switch ($extension) {
            case 'js':
                return $this->getScriptTag($filePath, $options);

            case 'css':
                return $this->getStylesheetTag($filePath, $options);
        }

        return '';
    }

    /**
     * @param string $path
     * @param array $options
     *
     * @return string
     */
    protected function getScriptTag($path, array $options = [])
    {
        $type = (isset($options['type']) && !empty($options['type'])) ? $options['type'] : 'text/javascript';

        return \sprintf(
            '<script type="%s" src="%s"></script>',
            $this->escape($type),
            $this->escape($path)
        );
    }

    /**
     * @param string $path
     * @param array $options
     *
     * @return string
     */
    protected function getStylesheetTag($path, array $options = [])
    {
        $media = (isset($options['media']) && !empty($options['media'])) ? $options['media'] : 'screen';
        $type = (isset($options['type']) && !empty($options['type'])) ? $options['type'] : 'text/css';
        $rel = (isset($options['rel']) && !empty($options['rel'])) ? $options['rel'] : 'stylesheet';

        return \sprintf(
            '<link href="%s" media="%s" rel="%s" type="%s">',
            $this->escape($path),
            $this->escape($media),
            $this->escape($rel),
            $this->escape($type)
        );
    }
}
