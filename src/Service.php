<?php

namespace AsseticBundle;

use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Cache\FilesystemCache;
use Assetic\Factory;
use Assetic\Factory\Worker\WorkerInterface;
use Assetic\Filter\FilterInterface;
use Assetic\FilterManager as AsseticFilterManager;
use AsseticBundle\View\StrategyInterface;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 * Class Service
 * @package AsseticBundle
 */
class Service
{
    const DEFAULT_ROUTE_NAME = 'default';

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $controllerName;

    /**
     * @var string
     */
    protected $actionName;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var StrategyInterface[]
     */
    protected $strategy = [];

    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @var AssetWriter
     */
    protected $assetWriter;

    /**
     * @var WorkerInterface
     */
    protected $cacheBusterStrategy;

    /**
     * @var AsseticFilterManager
     */
    protected $filterManager;

    /**
     * Service constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return (null === $this->routeName) ? self::DEFAULT_ROUTE_NAME : $this->routeName;
    }

    /**
     * @param AssetManager $assetManager
     */
    public function setAssetManager(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    /**
     * @return AssetManager
     */
    public function getAssetManager()
    {
        if (null === $this->assetManager) {
            $this->assetManager = new AssetManager();
        }

        return $this->assetManager;
    }

    /**
     * @return AssetWriter
     */
    public function getAssetWriter()
    {
        if (null === $this->assetWriter) {
            $this->assetWriter = new AssetWriter(
                $this->configuration->getWebPath()
            );
        }

        return $this->assetWriter;
    }

    /**
     * @param AssetWriter $assetWriter
     */
    public function setAssetWriter($assetWriter)
    {
        $this->assetWriter = $assetWriter;
    }

    /**
     * @return WorkerInterface
     */
    public function getCacheBusterStrategy()
    {
        return $this->cacheBusterStrategy;
    }

    /**
     * @param WorkerInterface $cacheBusterStrategy
     * @return $this
     */
    public function setCacheBusterStrategy(WorkerInterface $cacheBusterStrategy)
    {
        $this->cacheBusterStrategy = $cacheBusterStrategy;

        return $this;
    }

    /**
     * @param AsseticFilterManager $filterManager
     */
    public function setFilterManager(AsseticFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * @return AsseticFilterManager
     */
    public function getFilterManager()
    {
        if (null === $this->filterManager) {
            $this->filterManager = new AsseticFilterManager();
        }

        return $this->filterManager;
    }

    /**
     * @param string $controllerName
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Build collection of assets.
     */
    public function build()
    {
        $moduleConfiguration = $this->configuration->getModules();

        foreach ($moduleConfiguration as $configuration) {
            $factory = $this->createAssetFactory($configuration);
            $collections = (array)$configuration['collections'];

            foreach ($collections as $name => $options) {
                $this->prepareCollection($options, $name, $factory);
            }
        }
    }

    /**
     * @param AssetInterface $asset
     * @return AssetCache|AssetInterface
     */
    private function cacheAsset(AssetInterface $asset)
    {
        return $this->configuration->getCacheEnabled()
            ? new AssetCache($asset, new FilesystemCache($this->configuration->getCachePath()))
            : $asset;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function initFilters(array $filters)
    {
        $result = [];

        $fm = $this->getFilterManager();

        foreach ($filters as $alias => $options) {
            $option = null;
            if (is_array($options)) {
                if (!isset($options['name'])) {
                    throw new Exception\InvalidArgumentException(
                        'Filter "' . $alias . '" required option "name"'
                    );
                }

                $name = $options['name'];
                $option = isset($options['option']) ? $options['option'] : null;
            } elseif (is_string($options)) {
                $name = $options;
                unset($options);
            }

            if (!isset($name)) {
                continue;
            }

            if (is_numeric($alias)) {
                $alias = $name;
            }

            // Filter Id should have optional filter indicator "?"
            $filterId = ltrim($alias, '?');

            if (!$fm->has($filterId)) {
                if (is_array($option) && !empty($option)) {
                    $r = new \ReflectionClass($name);
                    /** @var FilterInterface $filter */
                    $filter = $r->newInstanceArgs($option);
                } elseif ($option) {
                    $filter = new $name($option);
                } else {
                    $filter = new $name();
                }

                $fm->set($filterId, $filter);
            }

            $result[] = $alias;
        }

        return $result;
    }

    /**
     * @param Renderer $renderer
     * @return bool
     */
    public function setupRenderer(Renderer $renderer)
    {
        $controllerConfig = $this->getControllerConfig();
        $actionConfig = $this->getActionConfig();
        $config = array_merge($controllerConfig, $actionConfig);

        if (count($config) == 0) {
            $config = $this->getRouterConfig();
        }

        // If we don't have any assets listed by now, or if we are mixing in
        // the default assets, then merge in the default assets to the config
        // array
        $defaultConfig = $this->getDefaultConfig();
        if (count($config) == 0
            || (isset($defaultConfig['options']['mixin'])
                && $defaultConfig['options']['mixin'])
        ) {
            $config = array_merge($defaultConfig['assets'], $config);
        }

        if (count($config) > 0) {
            $this->setupRendererFromOptions($renderer, $config);
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getDefaultConfig()
    {
        $defaultDefinition = $this->configuration->getDefault();

        return $defaultDefinition ? $defaultDefinition : [];
    }

    /**
     * @return array|null
     */
    public function getRouterConfig()
    {
        $assetOptions = $this->configuration->getRoute($this->getRouteName());
        return $assetOptions ? $assetOptions : [];
    }

    /**
     * @return array|null
     */
    public function getControllerConfig()
    {
        $assetOptions = $this->configuration->getController(
            $this->getControllerName()
        );

        if ($assetOptions) {
            if (array_key_exists('actions', $assetOptions)) {
                unset($assetOptions['actions']);
            }

            return $assetOptions;
        }

        return [];
    }

    /**
     * @return array
     */
    public function getActionConfig()
    {
        $assetOptions = $this->configuration->getController(
            $this->getControllerName()
        );
        $actionName = $this->getActionName();

        if ($assetOptions && array_key_exists('actions', $assetOptions)
            && array_key_exists($actionName, $assetOptions['actions'])
        ) {
            return $assetOptions['actions'][$actionName];
        }

        return [];
    }

    /**
     * @param Renderer $renderer
     * @param array $options
     */
    public function setupRendererFromOptions(Renderer $renderer, array $options)
    {
        if (!$this->hasStrategyForRenderer($renderer)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'No strategy defined for renderer "%s"',
                    $this->getRendererName($renderer)
                )
            );
        }

        /** @var $strategy \AsseticBundle\View\StrategyInterface */
        $strategy = $this->getStrategyForRenderer($renderer);

        while ($assetAlias = array_shift($options)) {
            $assetAlias = ltrim($assetAlias, '@');

            /** @var $asset \Assetic\Asset\AssetInterface */
            $asset = $this->assetManager->get($assetAlias);
            // Prepare view strategy
            $strategy->setupAsset($asset);
        }
    }

    /**
     * @param \Zend\View\Renderer\RendererInterface $renderer
     *
     * @return bool
     */
    public function hasStrategyForRenderer(Renderer $renderer)
    {
        $rendererName = $this->getRendererName($renderer);

        return (bool)$this->configuration->getStrategyNameForRenderer(
            $rendererName
        );
    }

    /**
     * Get strategy to setup assets for given $renderer.
     *
     * @param \Zend\View\Renderer\RendererInterface $renderer
     *
     * @throws Exception\DomainException
     * @throws Exception\InvalidArgumentException
     *
     * @return \AsseticBundle\View\StrategyInterface|null
     */
    public function getStrategyForRenderer(Renderer $renderer)
    {
        if (!$this->hasStrategyForRenderer($renderer)) {
            return null;
        }

        $rendererName = $this->getRendererName($renderer);

        if (!isset($this->strategy[$rendererName])) {
            $strategyClass = $this->configuration->getStrategyNameForRenderer($rendererName);
            if (!class_exists($strategyClass, true)) {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'Strategy class "%s" doesn\'t exist',
                        $strategyClass
                    )
                );
            }

            $instance = new $strategyClass();

            if (!($instance instanceof StrategyInterface)) {
                throw new Exception\DomainException(
                    sprintf(
                        'Strategy class "%s" is not instanceof "%s"',
                        $strategyClass,
                        StrategyInterface::class
                    )
                );
            }

            $this->strategy[$rendererName] = $instance;
        }

        /** @var $strategy \AsseticBundle\View\StrategyInterface */
        $strategy = $this->strategy[$rendererName];
        $strategy->setBaseUrl($this->configuration->getBaseUrl());
        $strategy->setBasePath($this->configuration->getBasePath());
        $strategy->setDebug($this->configuration->isDebug());
        $strategy->setCombine($this->configuration->isCombine());
        $strategy->setRenderer($renderer);

        return $strategy;
    }

    /**
     * Get renderer name from $renderer object.
     *
     * @param \Zend\View\Renderer\RendererInterface $renderer
     *
     * @return string
     */
    public function getRendererName(Renderer $renderer)
    {
        return get_class($renderer);
    }

    /**
     * Gets the service configuration.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     *
     * @return Factory\AssetFactory
     */
    public function createAssetFactory(array $configuration)
    {
        $factory = new Factory\AssetFactory($configuration['root_path']);
        $factory->setAssetManager($this->getAssetManager());
        $factory->setFilterManager($this->getFilterManager());
        $worker = $this->getCacheBusterStrategy();
        if ($worker instanceof WorkerInterface) {
            $factory->addWorker($worker);
        }
        $factory->setDebug($this->configuration->isDebug());

        return $factory;
    }

    /**
     * @param AssetCollection $asset
     * @param string|null $targetPath
     * @param Factory\AssetFactory $factory
     * @param bool $disableSourcePath
     */
    public function moveRaw(AssetCollection $asset,
                            $targetPath,
                            Factory\AssetFactory $factory,
                            $disableSourcePath = false)
    {
        /** @var $value AssetInterface */
        foreach ($asset as $value) {
            if ($disableSourcePath) {
                $value->setTargetPath(
                    ($targetPath ? $targetPath : '') . basename($value->getSourcePath())
                );
            } else {
                $value->setTargetPath(
                    ($targetPath ? $targetPath : '') . $value->getSourcePath()
                );
            }

            $value = $this->cacheAsset($value);
            $this->writeAsset($value, $factory);
        }
    }

    /**
     * @param array $options
     * @param string $name
     * @param Factory\AssetFactory $factory
     *
     * @return void
     */
    public function prepareCollection($options, $name, Factory\AssetFactory $factory)
    {
        $assets = isset($options['assets']) ? $options['assets'] : [];
        $filters = isset($options['filters']) ? $options['filters'] : [];
        $options = isset($options['options']) ? $options['options'] : [];
        $options['output'] = isset($options['output']) ? $options['output'] : $name;
        $moveRaw = isset($options['move_raw']) && $options['move_raw'];
        $targetPath = !empty($options['targetPath']) ? $options['targetPath'] : '';

        if (substr($targetPath, -1) != DIRECTORY_SEPARATOR) {
            $targetPath .= DIRECTORY_SEPARATOR;
        }

        $filters = $this->initFilters($filters);
        $asset = $factory->createAsset(
            $assets,
            $filters,
            $options
        );

        // Allow to move all files 1:1 to new directory
        // its particularly useful when this assets are i.e. images.
        if ($moveRaw) {
            if (isset($options['disable_source_path'])) {
                $this->moveRaw(
                    $asset,
                    $targetPath,
                    $factory,
                    $options['disable_source_path']
                );
            } else {
                $this->moveRaw(
                    $asset,
                    $targetPath,
                    $factory
                );
            }
        } else {
            $asset = $this->cacheAsset($asset);
            $this->assetManager->set($name, $asset);
            // Save asset on disk
            $this->writeAsset($asset, $factory);
        }
    }

    /**
     * Write $asset to public directory.
     *
     * @param AssetInterface $asset Asset to write
     * @param Factory\AssetFactory $factory The factory this asset was generated with
     */
    public function writeAsset(AssetInterface $asset, Factory\AssetFactory $factory)
    {
        // We're not interested in saving assets on request
        if (!$this->configuration->getBuildOnRequest()) {
            return;
        }

        // Write asset on disk on every request
        if (!$this->configuration->getWriteIfChanged()) {
            $this->write($asset, $factory);
            return;
        }

        $target = $this->configuration->getWebPath($asset->getTargetPath());
        $created = is_file($target);
        $isChanged = $created && filemtime($target) < $factory->getLastModified($asset);

        // And long requested optimization
        if (!$created || $isChanged) {
            $this->write($asset, $factory);
        }
    }

    /**
     * @param AssetInterface $asset Asset to write
     * @param Factory\AssetFactory $factory The factory this asset was generated with
     */
    protected function write(AssetInterface $asset, Factory\AssetFactory $factory)
    {
        if ($this->configuration->isDebug()
            && !$this->configuration->isCombine()
            && ($asset instanceof AssetCollection)
        ) {
            /** @var AssetInterface $item */
            foreach ($asset as $item) {
                $this->writeAsset($item, $factory);
            }

        } else {

            $this->getAssetWriter()->writeAsset($asset);
        }

        $this->setPermission($asset);
    }

    /**
     * @param AssetInterface $asset Asset was wrote
     */
    protected function setPermission(AssetInterface $asset)
    {
        $target = $this->configuration->getWebPath($asset->getTargetPath());

        if (is_file($target)) {
            if ($this->configuration->getFilePermission() !== null) {
                chmod($target, $this->configuration->getFilePermission());
            }

            $baseDir = dirname($asset->getTargetPath());

        } else {

            $baseDir = $asset->getTargetPath();
        }

        if ($this->configuration->getDirPermission() === null) {
            return;
        }

        $dirNames = explode('/', rtrim($baseDir, '/'));

        $dPerm = $this->configuration->getDirPermission();
        $dirName = [];

        foreach ($dirNames as $item) {
            $dirName[] = $item;
            $path = $this->configuration->getWebPath(implode('/', $dirName));
            chmod($path, $dPerm);
        }
    }
}
