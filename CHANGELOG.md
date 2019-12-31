# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.2 - 2017-05-17

### Added

- Nothing.

### Changes

- [zendframework/zend-filter#40](https://github.com/zendframework/zend-filter/pull/40) updates the
  `Callback` filter's `setCallback()` method to allow passing a string name of a
  class that is instantiable without constructor arguments, and which defines
  `__invoke()`.
- [zendframework/zend-filter#43](https://github.com/zendframework/zend-filter/pull/43) updates the
  exception thrown by the `File\Rename` filter when the target already exists to
  indicate the target filename path.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-filter#56](https://github.com/zendframework/zend-filter/pull/56) fixes how the
  `FilterPluginManagerFactory` factory initializes the plugin manager instance,
  ensuring it is injecting the relevant configuration from the `config` service
  and thus seeding it with configured translator loader services. This means
  that the `filters` configuration will now be honored in non-laminas-mvc contexts.
- [zendframework/zend-filter#36](https://github.com/zendframework/zend-filter/pull/36) fixes an issue in
  the constructor whereby a discovered option was not removed from the options
  list after being used to set the compression algorithm.
- [zendframework/zend-filter#49](https://github.com/zendframework/zend-filter/pull/49) and
  [zendframework/zend-filter#51](https://github.com/zendframework/zend-filter/pull/51) fix logic within
  the `Boolean` and `ToNull` filters to use boolean rather than arithmetic
  operations, ensuring that if the same type is specified multiple times via the
  options, it will be aggregated correctly internally, and thus ensure correct
  operation of the filter.
- [zendframework/zend-filter#55](https://github.com/zendframework/zend-filter/pull/55) adds a missing
  import statement to the `Word\SeparatorToSeparatorFactory`.

## 2.7.1 - 2016-04-18

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-filter#27](https://github.com/zendframework/zend-filter/pull/27) fixes the
  `Module::init()` method to properly receive a `ModuleManager` instance, and
  not expect a `ModuleEvent`.

## 2.7.0 - 2016-04-06

### Added

- [zendframework/zend-filter#25](https://github.com/zendframework/zend-filter/pull/25) exposes the
  package as a Laminas component and/or generic configuration provider, by adding the
  following:
  - `FilterPluginManagerFactory`, which can be consumed by container-interop /
    laminas-servicemanager to create and return a `FilterPluginManager` instance.
  - `ConfigProvider`, which maps the service `FilterManager` to the above
    factory.
  - `Module`, which does the same as `ConfigProvider`, but specifically for
    laminas-mvc applications. It also provices a specification to
    `Laminas\ModuleManager\Listener\ServiceListener` to allow modules to provide
    filter configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-02-08

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-filter#24](https://github.com/zendframework/zend-filter/pull/24) updates the
  `FilterPluginManager` to reference the `NumberFormat` **filter**, instead of
  the **view helper**.

## 2.6.0 - 2016-02-04

### Added

- [zendframework/zend-filter#14](https://github.com/zendframework/zend-filter/pull/14) adds the
  `UpperCaseWords` filter to the default list of filters known to the
  `FilterPluginManager`.
- [zendframework/zend-filter#22](https://github.com/zendframework/zend-filter/pull/22) adds
  documentation, and automatically publishes it to
  https://docs.laminas.dev/laminas-filter/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-filter#15](https://github.com/zendframework/zend-filter/pull/15),
  [zendframework/zend-filter#19](https://github.com/zendframework/zend-filter/pull/19), and
  [zendframework/zend-filter#21](https://github.com/zendframework/zend-filter/pull/21)
  update the component to be forwards-compatible with laminas-servicemanager v3,
  and reduce the number of development dependencies required for testing.
