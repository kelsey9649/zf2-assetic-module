<?php

namespace AsseticBundle\Factory;

use AsseticBundle\FilterManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FilterManagerFactory
 * @package AsseticBundle\Factory
 */
class FilterManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $locator
     * @param string $requestedName
     * @param array $options, optional
     *
     * @return \AsseticBundle\FilterManager
     */
    public function __invoke(ContainerInterface $locator, $requestedName, array $options = null)
    {
        $filterManager = new FilterManager($locator);

        return $filterManager;
    }

    /**
     * @param ServiceLocatorInterface $locator
     *
     * @return \AsseticBundle\FilterManager
     */
    public function createService(ServiceLocatorInterface $locator)
    {
        return $this($locator, FilterManager::class);
    }
}
