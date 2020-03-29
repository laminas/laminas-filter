# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.9.4 - 2020-03-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zendframework/zend-filter:^2.9.2`.

## 2.9.3 - 2020-01-07

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/laminas/laminas-filter/pull/15) fixes an issue caused by migration, whereby the `Lzf` compression adapter was incorrectly renamed to `Llaminas`, and all invocations of `lzf_*` functions were renamed to `llaminas_*`. These are now corrected, and patch releases issued for all prior releases.

## 2.9.2 - 2019-08-19

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-filter#89](https://github.com/zendframework/zend-filter/pull/89) fixes infinite
  loop on malformed HTML comments in StripTags filter.

- [zendframework/zend-filter#92](https://github.com/zendframework/zend-filter/pull/92) fixes Tar adapter
  to not require `archive` in options when decompressing.

## 2.9.1 - 2018-12-17

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-filter#79](https://github.com/zendframework/zend-filter/pull/79) fixes a regression introduced in 2.9.0 when using
  `Laminas\Filter\File\RenameUpload` via the traditional SAPI.

## 2.9.0 - 2018-12-12

### Added

- [zendframework/zend-filter#70](https://github.com/zendframework/zend-inputfilter/pull/70) Adds compatibility with the PSR-7 `UploadedFileInterface` to the
  `RenameUpload` filter. The functionality requires PHP 7 and a
  psr/http-factory-implementation in your application. When present,
  `RenameUpload` will accept a PSR-7 `UploadedFileInterface`, and return a new
  one representing the renamed file.

- [zendframework/zend-filter#71](https://github.com/zendframework/zend-filter/pull/71) adds the `ToFloat` filter, to complement the `ToInt` filter.

- [zendframework/zend-filter#69](https://github.com/zendframework/zend-filter/pull/69) adds `Laminas\Filter\StringSufix`; when provided with a string `suffix`
  option, it will suffix scalar values with that string.

- [zendframework/zend-filter#69](https://github.com/zendframework/zend-filter/pull/69) adds `Laminas\Filter\StringPrefix`; when provided with a string `prefix`
  option, it will prefix scalar values with that string.

### Changed

- [zendframework/zend-filter#66](https://github.com/zendframework/zend-filter/pull/66) modifies how the FilterPluginManager is registered with the dependency
  injection container. Previously, it was registered only under the name
  `FilterManager`. Now it regisers `Laminas\Filter\FilterPluginManager` as a
  factory service, and `FilterManager` as an alias to that service.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.8.0 - 2018-04-11

### Added

- [zendframework/zend-filter#26](https://github.com/zendframework/zend-filter/pull/26) adds the interface
  `Laminas\Filter\FilterProviderInterface`, which can be used to provide
  configuration for the `FilterPluginManager` via laminas-mvc `Module` classes.

- [zendframework/zend-filter#61](https://github.com/zendframework/zend-filter/pull/61) adds support for
  PHP 7.2.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-filter#61](https://github.com/zendframework/zend-filter/pull/61) removes support
  for PHP 5.5.

- [zendframework/zend-filter#61](https://github.com/zendframework/zend-filter/pull/61) removes support
  for HHVM.

- [zendframework/zend-filter#61](https://github.com/zendframework/zend-filter/pull/61) removes support
  for laminas-crypt versions prior to 3.0. This was done as PHP deprecated the
  mcrypt extension starting in PHP 7.1, and does not ship it by default
  starting in PHP 7.2. laminas-crypt 3.0 adds an OpenSSL adapter for its
  BlockCipher capabilities, and acts as a polyfill for mcrypt usage. Since this
  functionality has been used by default since 2.7.2, users should be able to
  upgrade seamlessly.

### Fixed

- Nothing.

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
