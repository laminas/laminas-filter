# Providing Filters via Modules

If you wish to indicate that your laminas-mvc module provides filters, have your `Module` class implement `Laminas\ModuleManager\Feature\FilterProviderInterface`, which defines the method:

```php
/**
 * @return array
 */
public function getFilterConfig();
```

The method should return an array of configuration following the [laminas-servicemanager configuration format](https://docs.laminas.dev/laminas-servicemanager/configuring-the-service-manager/).

Further information can be found in the [Module Manager documentation](https://docs.laminas.dev/laminas-modulemanager/module-manager/#servicelistener).

If you are not using laminas-mvc, but are using a dependency injection container (e.g., if you are using Mezzio), you can also provide filters using the top-level `filters` configuration key; the value of that key should be laminas-servicemanager configuration, as linked above.

(laminas-mvc users may also provide configuration in the same way, and omit implementation of the `FilterProviderInterface`.)
