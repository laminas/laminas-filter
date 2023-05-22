# Migration from Version 2 to 3

laminas-filter version 3 makes a number of changes that may affect your application.
This document details those changes, and provides suggestions on how to update your application to work with version 3.

## Removed Filters & Features

These filters were deprecated in the 2.0.x series of releases and have now been removed:

- `Laminas\Filter\File\Decrypt`
- `Laminas\Filter\File\Encrypt`
- `Laminas\Filter\Blacklist`
- `Laminas\Filter\Decrypt`
- `Laminas\Filter\Encrypt`
- `Laminas\Filter\StaticFilter`
- `Laminas\Filter\Whitelist`

`Laminas\Filter\Compress` no longer supports the compression formats `Lzf`, `Rar` and `Snappy`.
Support for these formats has been removed so the following classes are no longer available:

- `Laminas\Filter\Compress\Lzf`
- `Laminas\Filter\Compress\Rar`
- `Laminas\Filter\Compress\Snappy`
