<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\ServiceManager;

/**
 * @deprecated Since version 2.15.0 This filter will be removed in version 3.0.0 of this component without replacement.
 */
class StaticFilter
{
    /** @var FilterPluginManager|null */
    protected static $plugins;

    /**
     * Set plugin manager for resolving filter classes
     *
     * @return void
     */
    public static function setPluginManager(?FilterPluginManager $manager = null)
    {
        static::$plugins = $manager;
    }

    /**
     * Get plugin manager for loading filter classes
     *
     * @return FilterPluginManager
     */
    public static function getPluginManager()
    {
        $plugins = static::$plugins;

        if (! $plugins instanceof FilterPluginManager) {
            $plugins = new FilterPluginManager(new ServiceManager());
            static::setPluginManager($plugins);
        }

        return $plugins;
    }

    /**
     * Returns a value filtered through a specified filter class, without requiring separate
     * instantiation of the filter object.
     *
     * The first argument of this method is a data input value, that you would have filtered.
     * The second argument is a string, which corresponds to the basename of the filter class,
     * relative to the Laminas\Filter namespace. This method automatically loads the class,
     * creates an instance, and applies the filter() method to the data input. You can also pass
     * an array of constructor arguments, if they are needed for the filter class.
     *
     * @param  string       $classBaseName
     * @param  array        $args          OPTIONAL
     * @return mixed
     * @throws Exception\ExceptionInterface
     */
    public static function execute(mixed $value, $classBaseName, array $args = [])
    {
        $plugins = static::getPluginManager();

        $filter = $plugins->get($classBaseName, $args);

        return $filter instanceof FilterInterface
            ? $filter->filter($value)
            : $filter($value);
    }
}
