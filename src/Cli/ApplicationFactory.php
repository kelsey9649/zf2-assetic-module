<?php

namespace AsseticBundle\Cli;

use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ApplicationFactory
 * @package AsseticBundle\Cli
 */
class ApplicationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options , optional
     *
     * @return Application
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $service = $container->get('AsseticService');

        $cliApplication = new Application(
            'AsseticBundle',
            '1.7.0'
        );

        $cliApplication->add(new BuildCommand($service));
        $cliApplication->add(new SetupCommand($service));

        return $cliApplication;
    }

    /**
     * @param ServiceLocatorInterface $locator
     *
     * @return Application
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function createService(ServiceLocatorInterface $locator)
    {
        return $this(
            $locator,
            'AsseticBundle\Cli'
        );
    }
}
