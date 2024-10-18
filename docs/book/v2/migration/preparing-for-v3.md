# Preparing for Version 3

Version 3 will introduce a number of backwards incompatible changes. This document is intended to help you prepare for these changes.

## Removed Features

### Inheritance Changes

Most filters will be closed to inheritance in v3 by employing the `final` keyword.
To prepare for this change, search for classes in your codebase that extend from any of the concrete filters and either re-implement them completely, or consider refactoring them to use composition instead of inheritance.

If you have extended an existing filter for a use-case that is not handled by this library, also consider sending a patch if you think that the library could benefit from your changes.

### `AbstractFilter` Removal

`Laminas\Filter\AbstractFilter` is now deprecated for removal in 3.0.
If you have created custom filters that inherit from `AbstractFilter`, remove the class from your inheritance tree and implement `FilterInterface`.
You should re-define the `__invoke` method previously inherited from `AbstractFilter` with:

```php
/** @inheritDoc */
public function __invoke(mixed $value): mixed
{
    return $this->filter($value);
}
```

You may also need to consider how options are handled, if your custom filter needs them.
This is because `AbstractFilter` provides various methods for setting and getting options at runtime rather than once during construction.
Typically, your constructor should accept an associative array where options should be validated and set once:

```php
use Laminas\Filter\FilterInterface;

final class MyFilter implements FilterInterface
{
    private readonly string $fooOption;
    
    public function __construct(array $options = [])
    {
        $this->fooOption = $options['foo'] ?? 'Some Default';
    }
    
    // ...
}
```

All the filters provided in `laminas-filter` either have been, or will be refactored to remove `AbstractFilter` from the inheritance hierarchy.
It may be useful to look at [merged PRs](https://github.com/laminas/laminas-filter/issues?q=is%3Aclosed+milestone%3A3.0.0) for examples for refactoring your own filters if necessary.

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
