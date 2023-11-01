# Migration from Version 2 to 3

laminas-filter version 3 makes a number of changes that may affect your application.
This document details those changes, and provides suggestions on how to update your application to work with version 3.

## Signature Changes

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

### Whitelist & Blacklist Filters

- `Laminas\Filter\Whitelist` has been replaced by [`Laminas\Filter\AllowList`](../standard-filters.md#allowlist)
- `Laminas\Filter\Blacklist` has been replaced by [`Laminas\Filter\DenyList`](../standard-filters.md#denylist)

## Removed Features

`Laminas\Filter\Compress` no longer supports the compression formats `Lzf`, `Rar` and `Snappy`.
Support for these formats has been removed so the following classes are no longer available:

- `Laminas\Filter\Compress\Lzf`
- `Laminas\Filter\Compress\Rar`
- `Laminas\Filter\Compress\Snappy`

The following compression formats are still available: `Bz2`, `Gz`, `Tar` and `Zip`
