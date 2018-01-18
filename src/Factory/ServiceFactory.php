<?php

namespace AsseticBundle\Factory;

use AsseticBundle\Configuration;
use AsseticBundle\Service;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ServiceFactory
 * @package AsseticBundle\Factory
 */
class ServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $locator
     * @param string $requestedName
     * @param array $options , optional
     *
     * @return \AsseticBundle\Service
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function __invoke(ContainerInterface $locator, $requestedName, array $options = null)
    {
        $asseticConfig = $locator->get(Configuration::class);

        if ($asseticConfig->detectBaseUrl()) {

            /** @var $request \Zend\Http\PhpEnvironment\Request */
            $request = $locator->get('Request');

            if (method_exists($request, 'getBaseUrl')) {
                $asseticConfig->setBaseUrl($request->getBaseUrl());
            }
        }

        $asseticService = new Service($asseticConfig);

        $asseticService->setAssetManager($locator->get('Assetic\AssetManager'));
        $asseticService->setAssetWriter($locator->get('Assetic\AssetWriter'));
        $asseticService->setFilterManager($locator->get('Assetic\FilterManager'));

        // Cache buster is not mandatory
        if ($locator->has('AsseticCacheBuster')) {

            $asseticService->setCacheBusterStrategy(
                $locator->get('AsseticCacheBuster')
            );
        }

        return $asseticService;
    }

    /**
     * @param ServiceLocatorInterface $locator
     *
     * @return \AsseticBundle\Service
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function createService(ServiceLocatorInterface $locator)
    {
        return $this($locator, Service::class);
    }
}
