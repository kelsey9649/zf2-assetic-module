<?php

namespace AsseticBundle;

use Zend\Stdlib;

/**
 * Class Configuration
 * @package AsseticBundle
 */
class Configuration extends Stdlib\AbstractOptions
{
    /**
     * Permits config arrays provided to the options to contain keys that are
     * not contained within the config. They are just ignored
     *
     * @var bool
     */
    protected $__strictMode__ = false;

    /**
     * Debug option that is passed to Assetic.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Combine option giving the opportunity not to combine the assets in debug mode.
     *
     * @var bool
     */
    protected $combine = true;

    /**
     * Flag indicating whether or not to build assets on request.
     *
     * @var bool
     */
    protected $buildOnRequest = true;

    /**
     * Full path to public directory where assets will be generated.
     *
     * @var string
     */
    protected $webPath;

    /**
     * Full path to cache directory.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Is cache enabled.
     *
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * The base url.
     *
     * By default this value is set from "\Zend\Http\PhpEnvironment\Request::getBaseUrl()"
     *
     * Example:
     * <code>
     * http://example.com/
     * </code>
     *
     * @var string|null
     */
    protected $baseUrl;

    /**
     * The base path.
     *
     * By default this value is set from "\Zend\Http\PhpEnvironment\Request::getBasePath()"
     *
     * Example:
     * <code>
     * <baseUrl>/~jdo/
     * </code>
     *
     * @var string|null
     */
    protected $basePath;

    /**
     * Asset will be saved on disk, only when it's modification time was changed
     *
     * @var bool
     */
    protected $writeIfChanged = true;

    /**
     * Default options.
     *
     * @var array
     */
    protected $default = [
        'assets'  => [],
        'options' => [],
    ];

    /**
     * Map of routes names and assets configuration.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Map of modules names and assets configuration.
     *
     * @var array
     */
    protected $modules = [];

    /**
     * Map of controllers names and assets configuration.
     *
     * @var array
     */
    protected $controllers = [];

    /**
     * Map of strategies that will be choose to setup Assetic\AssetInterface
     * for particular Zend\View\Renderer\RendererInterface
     *
     * @var array
     */
    protected $rendererToStrategy = [];

    /**
     * List of error types occurring in EVENT_DISPATCH_ERROR that will use
     * this module to render assets.
     *
     * @var array
     */
    protected $acceptableErrors = [];

    /**
     * @var null|int File permission for assetic files.
     */
    protected $filePermission = null;

    /**
     * @var null|int Directory permission for assetic directories.
     */
    protected $dirPermission = null;

    /**
     * Configuration constructor.
     * @param array|\Traversable|null $config
     */
    public function __construct($config = null)
    {
        if (null !== $config) {
            if (is_array($config)) {
                parent::__construct($config);
            } elseif ($config instanceof \Traversable) {
                parent::__construct(Stdlib\ArrayUtils::iteratorToArray($config));
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'Parameter to %s\'s constructor must be an array or implement the %s interface',
                        Configuration::class,
                        \Traversable::class
                    )
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param bool $flag
     */
    public function setDebug($flag)
    {
        $this->debug = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function isCombine()
    {
        return $this->combine;
    }

    /**
     * @param bool $flag
     */
    public function setCombine($flag)
    {
        $this->combine = (bool) $flag;
    }

    /**
     * @param string $path
     */
    public function setWebPath($path)
    {
        $this->webPath = $path;
    }

    /**
     * @param string|null $file
     * @return string
     */
    public function getWebPath($file = null)
    {
        if (null === $this->webPath) {
            throw new Exception\RuntimeException('Web path is not set');
        }

        if (null !== $file) {
            return rtrim($this->webPath, '/\\') . '/' . ltrim($file, '/\\');
        }

        return $this->webPath;
    }

    /**
     * @param string $path
     */
    public function setCachePath($path)
    {
        $this->cachePath = $path;
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * @param bool $cacheEnabled
     */
    public function setCacheEnabled($cacheEnabled)
    {
        $this->cacheEnabled = (bool) $cacheEnabled;
    }

    /**
     * @deprecated
     * @return bool
     */
    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * @param array $default
     */
    public function setDefault(array $default)
    {
        if (!isset($default['assets'])) {
            $default['assets'] = [];
        }

        if (!isset($default['options'])) {
            $default['options'] = [];
        }

        $this->default = $default;
    }

    /**
     * @return array
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param string $name
     * @param null $default
     * @return array|null
     */
    public function getRoute($name, $default = null)
    {
        $assets = [];
        $routeMatched = false;

        // Merge all assets configuration for which regular expression matches route
        foreach ($this->routes as $spec => $config) {
            if (preg_match('(^' . $spec . '$)i', $name)) {
                $routeMatched = true;
                $assets = Stdlib\ArrayUtils::merge($assets, (array) $config);
            }
        }

        // Only return default if none regular expression matched
        return $routeMatched ? $assets : $default;
    }

    /**
     * @param array $controllers
     */
    public function setControllers(array $controllers)
    {
        $this->controllers = $controllers;
    }

    /**
     * @return array
     */
    public function getControllers()
    {
        return $this->controllers;
    }

    /**
     * @param string $name
     * @param null $default
     * @return array|null
     */
    public function getController($name, $default = null)
    {
        return array_key_exists($name, $this->controllers)
            ? $this->controllers[$name]
            : $default;
    }

    /**
     * @param array $modules
     */
    public function setModules(array $modules)
    {
        $this->modules = [];
        foreach ($modules as $name => $options) {
            $this->addModule($name, $options);
        }
    }

    /**
     * @param $name
     * @param array $options
     */
    public function addModule($name, array $options)
    {
        $name = strtolower($name);
        $this->modules[$name] = $options;
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @param string $name
     * @param null $default
     * @return array|null
     */
    public function getModule($name, $default = null)
    {
        $name = strtolower($name);

        return array_key_exists($name, $this->modules)
            ? $this->modules[$name]
            : $default;
    }

    /**
     * @return bool
     */
    public function detectBaseUrl()
    {
        return (null === $this->baseUrl || 'auto' === $this->baseUrl);
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        if (null !== $baseUrl && 'auto' !== $baseUrl) {
            $baseUrl = rtrim($baseUrl, '/') . '/';
        }
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return null|string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param null|string $basePath
     */
    public function setBasePath($basePath)
    {
        if (null !== $basePath) {
            $basePath = trim($basePath, '/');
            $basePath = $basePath . '/';
        }
        $this->basePath = $basePath;
    }

    /**
     * @return null|string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param array $strategyForRenderer
     */
    public function setRendererToStrategy(array $strategyForRenderer)
    {
        $this->rendererToStrategy = $strategyForRenderer;
    }

    /**
     * @param string $rendererClass
     * @param string $strategyClass
     */
    public function addRendererToStrategy($rendererClass, $strategyClass)
    {
        $this->rendererToStrategy[$rendererClass] = $strategyClass;
    }

    /**
     * @param string $rendererName
     * @param null $default
     * @return mixed|null
     */
    public function getStrategyNameForRenderer($rendererName, $default = null)
    {
        return array_key_exists($rendererName, $this->rendererToStrategy)
            ? $this->rendererToStrategy[$rendererName]
            : $default;
    }

    /**
     * @param array $acceptableErrors
     */
    public function setAcceptableErrors(array $acceptableErrors)
    {
        $this->acceptableErrors = $acceptableErrors;
    }

    /**
     * @return array
     */
    public function getAcceptableErrors()
    {
        return $this->acceptableErrors;
    }

    /**
     * @return int|null
     */
    public function getFilePermission()
    {
        return $this->filePermission;
    }

    /**
     * @param null|int $filePermission
     */
    public function setFilePermission($filePermission)
    {
        $this->filePermission = null;
        if (is_int($filePermission)) {
            $this->filePermission = (int) $filePermission;
        }
    }

    /**
     * @return int|null
     */
    public function getDirPermission()
    {
        return $this->dirPermission;
    }

    /**
     * @param null|int $dirPermission
     */
    public function setDirPermission($dirPermission)
    {
        $this->dirPermission = null;
        if (is_int($dirPermission)) {
            $this->dirPermission = (int) $dirPermission;
        }
    }

    /**
     * @param bool $flag
     */
    public function setBuildOnRequest($flag)
    {
        $this->buildOnRequest = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getBuildOnRequest()
    {
        return $this->buildOnRequest;
    }

    /**
     * @param bool $flag
     */
    public function setWriteIfChanged($flag)
    {
        $this->writeIfChanged = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getWriteIfChanged()
    {
        return $this->writeIfChanged;
    }
}
