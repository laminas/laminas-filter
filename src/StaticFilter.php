<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\ServiceManager\ServiceManager;

class StaticFilter
{
    /** @var FilterPluginManager */
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
        if (null === static::$plugins) {
            static::setPluginManager(new FilterPluginManager(new ServiceManager()));
        }

        return static::$plugins;
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
     * @param  mixed        $value
     * @param  string       $classBaseName
     * @param  array        $args          OPTIONAL
     * @return mixed
     * @throws Exception\ExceptionInterface
     */
    public static function execute($value, $classBaseName, array $args = [])
    {
        $plugins = static::getPluginManager();

        $filter = $plugins->get($classBaseName, $args);
        return $filter->filter($value);
    }
}
