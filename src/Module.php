<?php

namespace AsseticBundle;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;

/**
 * Class Module
 * @package AsseticBundle
 */
class Module implements
        ConfigProviderInterface,
        BootstrapListenerInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param \Zend\EventManager\EventInterface|MvcEvent $e
     *
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function onBootstrap(EventInterface $e)
    {
        // Only attach the Listener if the request came in through http(s)
        if (PHP_SAPI !== 'cli') {

            $app = $e->getApplication();
            $services = $app->getServiceManager();
            $listener = $services->get(Listener::class);
            $listener->attach($app->getEventManager());
        }
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return require __DIR__ . '/../configs/module.config.php';
    }
}
