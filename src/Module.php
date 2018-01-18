<?php

namespace AsseticBundle;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

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
     * @param \Zend\EventManager\EventInterface $e
     *
     * @return void
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function onBootstrap(EventInterface $e)
    {
        /** @var $e \Zend\Mvc\MvcEvent */
        // Only attach the Listener if the request came in through http(s)
        if (PHP_SAPI !== 'cli') {
            $app = $e->getApplication();

            $app->getServiceManager()->get('AsseticBundle\Listener')->attach($app->getEventManager());
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
