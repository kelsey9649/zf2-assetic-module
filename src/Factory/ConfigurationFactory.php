<?php

namespace AsseticBundle\Factory;

use AsseticBundle\Configuration;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ConfigurationFactory
 * @package AsseticBundle\Factory
 */
class ConfigurationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Configuration|object
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configuration = $container->get('Configuration');

        if (isset($configuration['assetic_configuration'])) {
            return new Configuration($configuration['assetic_configuration']);
        }

        return new Configuration();
    }
}
