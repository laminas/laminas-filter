# Migration from Version 2 to 3

laminas-filter version 3 makes a number of changes that may affect your application.
This document details those changes, and provides suggestions on how to update your application to work with version 3.

## Signature and Behaviour Changes

### AbstractFilter

The deprecated method `hasPcreUnicodeSupport()` has been removed. This method had been irrelevant for a long time since reliable Unicode support in `preg_*` functions is available in all supported versions of PHP.

### FilterInterface

`Laminas\Filter\FilterInterface` now specifies `__invoke()` as well as `filter()` forcing all filters to also be invokable classes.

In practice this is unlikely to cause problems because `AbstractFilter`, from which most filters extend already implements this method. You will however, encounter issues if you have a custom filter implementing `FilterInterface` that lacks an `__invoke()` method.

Implementation is straight forward, and in most cases, adding the following method should suffice:

```php
final class MyFilter implements Laminas\Filter\FilterInterface {
    public function filter(mixed $value): mixed
    {
        // Filter things…
    }
    
    public function __invoke(mixed $value) : mixed
    {
        return $this->filter($value); 
    }
}
```

#### Change of Parameter and Return Type

Also note that the signature of both `filter()` and `__invoke()` has changed to include `mixed` as both parameter and return type, therefore, it will be necessary to add any missing parameter and return types to your custom filters.

### Filter Plugin Manager

As the library now requires `laminas/laminas-servicemanager` version 4, the inheritance hierarchy has changed for the plugin manager, however it is also now `final`.

In addition to this, the default filter aliases have changed:

- All legacy `Zend\Filter\FilterName` have been removed including the lowercased v2 FQCNs such as `zendfilterstringtolower`
- All lowercase FQCN aliases _(That were added for Service Manager v2)_ have been removed such as `laminasfilterstringtolower`

The impact of the removal of these aliases will not affect you if you use a FQCN to retrieve filters from the plugin manager. If you experience `ServiceNotFoundException` errors, audit your usage of filters and the strings you use to retrieve them from the plugin manager and replace any outdated values with either the FQCN of the filter or a known, configured alias.

### Changes to Individual Filters

#### `DashToSeparator`

The following methods have been removed:

- `setOptions`
- `getOptions`
- `isOptions`
- `setSeparator`
- `getSeparator`

The constructor now only accepts an associative array of [documented options](../word.md#dashToSeparator).

#### `DenyList`

The following methods have been removed:

- `setStrict`
- `getStrict`
- `setList`
- `getList`

The constructor now only accepts an associative array of [documented options](../standard-filters.md#denylist).

#### `PregReplace`

The following methods have been removed:

- `setPattern`
- `getPattern`
- `setReplacement`
- `getReplacement`

The constructor now only accepts an associative array of [documented options](../standard-filters.md#pregreplace).

Additionally, `$options['pattern']` _must_ be provided at construction time or an exception is thrown.

Exceptions for invalid or empty patterns are now thrown during construct rather than when the filter is invoked.

#### `StringPrefix`

The following methods have been removed:

- `setPrefix`
- `getPrefix`

The constructor now only accepts an associative array of [documented options](../standard-filters.md#stringprefix).

An exception is no longer thrown for a missing prefix option, instead an empty string is used by default.
This means that the filter will have the effect of simply casting scalar values to string when a prefix option is not provided.

The filter will now recursively process scalar array members. Previously, arrays would be returned unfiltered.

#### `StringSuffix`

The following methods have been removed:

- `setSuffix`
- `getSuffix`

The constructor now only accepts an associative array of [documented options](../standard-filters.md#stringsuffix).

An exception is no longer thrown for a missing suffix option, instead an empty string is used by default.
This means that the filter will have the effect of simply casting scalar values to string when a suffix option is not provided.

The filter will now recursively process scalar array members. Previously, arrays would be returned unfiltered.

#### `StringTrim`

The following methods have been removed:

- `setCharList`
- `getCharList`

The constructor now only accepts an associative array of [documented options](../standard-filters.md#stringtrim).

#### `ToNull`

The following methods have been removed:

- `setType`
- `getType`

The constructor now only accepts an associative array of [documented options](../standard-filters.md#tonull).

## Removed Filters

The following filters were deprecated in the 2.0.x series of releases and have now been removed:

### Encryption and Decryption related filters

These filters had become outdated. We recommend that you make use of a maintained encryption library and [write your own filters](../writing-filters.md) if you need to encrypt or decrypt content using the `FilterInterface` contract.

- `Laminas\Filter\File\Decrypt`
- `Laminas\Filter\File\Encrypt`
- `Laminas\Filter\Decrypt`
- `Laminas\Filter\Encrypt`

### Static Filter

`Laminas\Filter\StaticFilter` has been removed without replacement. Most filters are "new-able" so similar behaviour can be accomplished with:

```php
$filtered = (new \Laminas\Filter\HtmlEntities())('Nuts & Bolts');
```

For filters requiring more complex construction, we encourage you to make use of dependency injection and compose the filter itself, or the `FilterPluginManager`, for example:

```php
$pluginManager = $container->get(\Laminas\Filter\FilterPluginManager::class);
$filter = $pluginManager->get(\Laminas\Filter\HtmlEntities::class);
$filtered = $filter->filter('A String');
```

### Uri Normalize

`Laminas\Filter\UriNormalize` has been removed. As noted in the [v2 preparation guide](../../v2/migration/preparing-for-v3.md#urinormalize-filter-removal), `Laminas\Filter\ForceUriScheme` might be a sufficient replacement depending on your use-case.

### Whitelist & Blacklist Filters

- `Laminas\Filter\Whitelist` has been replaced by [`Laminas\Filter\AllowList`](../standard-filters.md#allowlist)
- `Laminas\Filter\Blacklist` has been replaced by [`Laminas\Filter\DenyList`](../standard-filters.md#denylist)

## Removed Features

### Final by default

Nearly all the shipped filters now have the final keyword applied to the class. Individual filters were not designed for inheritance, so if you have filters that do extend from any of the shipped filters, you will likely have to re-consider your design.

### Removal of supported compression formats

`Laminas\Filter\Compress` no longer supports the compression formats `Lzf`, `Rar` and `Snappy`.
Support for these formats has been removed so the following classes are no longer available:

- `Laminas\Filter\Compress\Lzf`
- `Laminas\Filter\Compress\Rar`
- `Laminas\Filter\Compress\Snappy`

The following compression formats are still available: `Bz2`, `Gz`, `Tar` and `Zip`

### Removal of the `AbstractUnicode` class

Various filters such as `StringToLower` and `StringToUpper` inherited from the abstract class `AbstractUnicode` whose purpose was to implement an `encoding` option.
This class has been removed and the affected filters no longer inherit from anything.
In order to provide consistent handling of the `encoding` option that has been re-implemented in these filters, a new class `EncodingOption` has been introduced which provides static methods to validate a given encoding option.
This change is unlikely to affect you, unless you have inherited from this class. In which case, you will need to implement the provision of an encoding option for your custom filter and remove `AbstractUnicode` from your inheritance tree.
