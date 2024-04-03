# Preparing for Version 3

Version 3 will introduce a number of backwards incompatible changes. This document is intended to help you prepare for these changes.

## Removed Features

### Inheritance Changes

Most filters will be closed to inheritance in v3 by employing the `final` keyword.
To prepare for this change, search for classes in your codebase that extend from any of the concrete filters and either re-implement them completely, or consider refactoring them to use composition instead of inheritance.

If you have extended an existing filter for a use-case that is not handled by this library, also consider sending a patch if you think that the library could benefit from your changes.

### Compression Filter Adapter Removal

The Lzf, Snappy and Rar compression adapters will be removed in version 3.0.
If you are currently using any of these compression formats with laminas-filter, you will need to either use an alternative format such as Zip, Tar, Gz or Bz2, or, write a custom adapter to support your desired compression format.

### Encryption & Decryption Filter Removal

These filters have become outdated and will be removed in version 3.0 of this library. We recommend that you make use of a maintained encryption library and [write your own filters](../writing-filters.md) if you need to encrypt or decrypt content using the `FilterInterface` contract.

- `Laminas\Filter\File\Decrypt`
- `Laminas\Filter\File\Encrypt`
- `Laminas\Filter\Decrypt`
- `Laminas\Filter\Encrypt`

### Static Filter Removal

`Laminas\Filter\StaticFilter` will be removed without replacement in v3. Most filters are "new-able" so similar behaviour can be accomplished with:

```php
$filtered = (new \Laminas\Filter\HtmlEntities())('Nuts & Bolts');
```

For filters requiring more complex construction, we encourage you to make use of dependency injection and compose the filter itself, or via the `FilterPluginManager`, for example:

```php
$pluginManager = $container->get(\Laminas\Filter\FilterPluginManager::class);
$filter = $pluginManager->get(\Laminas\Filter\HtmlEntities::class);
$filtered = $filter->filter('A String');
```

### Whitelist & Blacklist Filter Removal

The deprecated filters `Whitelist` & `Blacklist` will be removed in v3 for their more favourably named counterparts `AllowList` and `DenyList`

- `Laminas\Filter\Whitelist` has been replaced by [`Laminas\Filter\AllowList`](../standard-filters.md#allowlist)
- `Laminas\Filter\Blacklist` has been replaced by [`Laminas\Filter\DenyList`](../standard-filters.md#denylist)

### UriNormalize Filter Removal

The [UriNormalize](../standard-filters.md#urinormalize) filter will be removed in version 3, primarily because its functionality is provided by `laminas-uri` which is no longer maintained.

There is not a direct replacement, but, if you were using the filter to normalize URL schemes, this functionality has been preserved in a new filter [ForceUriScheme](../standard-filters.md#forceurischeme).
