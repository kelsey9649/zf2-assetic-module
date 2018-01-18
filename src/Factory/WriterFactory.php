<?php

namespace AsseticBundle\Factory;

use Assetic\AssetWriter;
use AsseticBundle\Configuration;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class WriterFactory
 * @package AsseticBundle\Factory
 */
class WriterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $locator
     * @param string $requestedName
     * @param array $options , optional
     *
     * @return AssetWriter
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function __invoke(ContainerInterface $locator, $requestedName, array $options = null)
    {
        $asseticConfig = $locator->get(Configuration::class);
        $asseticWriter = new AssetWriter($asseticConfig->getWebPath());

        return $asseticWriter;
    }

    /**
     * @param ServiceLocatorInterface $locator
     *
     * @return AssetWriter
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function createService(ServiceLocatorInterface $locator)
    {
        return $this($locator, AssetWriter::class);
    }
}
