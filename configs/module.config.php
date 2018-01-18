<?php

use AsseticBundle\Cli\ApplicationFactory;
use AsseticBundle\Factory;
use AsseticBundle\View\NoneStrategy;
use AsseticBundle\View\ViewHelperStrategy;
use Zend\Mvc\Application;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\View\Renderer\FeedRenderer;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\Renderer\PhpRenderer;

return [
    'service_manager' => [
        'aliases' => [
            'AsseticConfiguration'  => \AsseticBundle\Configuration::class,
            'AsseticService'        => \AsseticBundle\Service::class,
            'Assetic\FilterManager' => \AsseticBundle\FilterManager::class,
        ],
        'factories' => [
            \AsseticBundle\Service::class       => Factory\ServiceFactory::class,
            \Assetic\AssetWriter::class         => Factory\WriterFactory::class,
            \AsseticBundle\FilterManager::class => Factory\FilterManagerFactory::class,
            \Assetic\AssetManager::class        => InvokableFactory::class,
            \AsseticBundle\Listener::class      => InvokableFactory::class,
            'AsseticBundle\Cli'                 => ApplicationFactory::class,
            \AsseticBundle\Configuration::class => Factory\ConfigurationFactory::class,
        ],
    ],
    'assetic_configuration' => [
        'rendererToStrategy' => [
            PhpRenderer::class  => ViewHelperStrategy::class,
            FeedRenderer::class => NoneStrategy::class,
            JsonRenderer::class => NoneStrategy::class,
        ],
        'acceptableErrors' => [
            Application::ERROR_CONTROLLER_NOT_FOUND,
            Application::ERROR_CONTROLLER_INVALID,
            Application::ERROR_ROUTER_NO_MATCH
        ],
    ],
];
