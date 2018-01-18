<?php

namespace AsseticBundle;

use Assetic\Filter\FilterInterface;
use Assetic\FilterManager as AsseticFilterManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class FilterManager
 * @package AsseticBundle
 */
class FilterManager extends AsseticFilterManager
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $locator
     */
    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    /**
     * @param $alias
     *
     * @return bool
     */
    public function has($alias)
    {
        return parent::has($alias) ? true : $this->serviceLocator->has($alias);
    }

    /**
     * @param $alias
     *
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    public function get($alias)
    {
        if (parent::has($alias)) {
            return parent::get($alias);
        }

        $services = $this->serviceLocator;

        if (!$services->has($alias)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A "%s" filter was not found in the container.',
                    $alias
                )
            );
        }

        $filter = $services->get($alias);

        if (!($filter instanceof FilterInterface)) {
            $givenType = is_object($filter) ? get_class($filter) : gettype($filter);
            throw new \InvalidArgumentException(
                sprintf(
                    'Retrieved filter "%s" is not instanceof "%s", but "%s" was given',
                    $alias,
                    FilterInterface::class,
                    $givenType
                )
            );
        }

        $this->set($alias, $filter);

        return $filter;
    }
}
