# Standard Filters

laminas-filter comes with a standard set of filters, available for immediate use.

## AllowList

This filter will return `null` if the value being filtered is not present the
filter's allowed list of values. If the value is present, it will return that
value.

For the opposite functionality see the [DenyList](#denylist) filter.

### Supported Options

The following options are supported for `Laminas\Filter\AllowList`:

- `strict`: Uses strict mode for comparisons; passed to `in_array()`'s third argument.
- `list`: An array of allowed values.

### Basic Usage

```php
$allowList = new \Laminas\Filter\AllowList([
    'list' => ['allowed-1', 'allowed-2']
]);
echo $allowList->filter('allowed-2');   // => 'allowed-2'
echo $allowList->filter('not-allowed'); // => null
```

## BaseName

`Laminas\Filter\BaseName` allows you to filter a string which contains the path to
a file, and it will return the base name of this file.

### Supported Options

There are no additional options for `Laminas\Filter\BaseName`.

### Basic Usage

```php
$filter = new Laminas\Filter\BaseName();

print $filter->filter('/vol/tmp/filename');
```

This will return 'filename'.

```php
$filter = new Laminas\Filter\BaseName();

print $filter->filter('/vol/tmp/filename.txt');
```

This will return '`filename.txt`'.

## Boolean

This filter changes a given input to be a `BOOLEAN` value. This is often useful when working with
databases or when processing form values.

### Supported Options

The following options are supported for `Laminas\Filter\Boolean`:

- `casting`: When this option is set to `TRUE`, then any given input will be
  cast to boolean.  This option defaults to `TRUE`.
- `translations`: This option sets the translations which will be used to detect localized input.
- `type`: The `type` option sets the boolean type which should be used. Read
  the following for details.

### Default Behavior

By default, this filter works by casting the input to a `BOOLEAN` value; in other words, it operates
in a similar fashion to calling `(boolean) $value`.

```php
$filter = new Laminas\Filter\Boolean();
$value  = '';
$result = $filter->filter($value);
// returns false
```

This means that without providing any configuration, `Laminas\Filter\Boolean` accepts all input types
and returns a `BOOLEAN` just as you would get by type casting to `BOOLEAN`.

### Changing the Default Behavior

Sometimes, casting with `(boolean)` will not suffice. `Laminas\Filter\Boolean`
allows you to configure specific types to convert, as well as which to omit.

The following types can be handled (in this precedence order):

- `localized`: Converts any string as mapped (case sensitive) in the `translations` option.
- `false`: Converts a string equal to the word "false" (case insensitive) to boolean `FALSE`.
- `null`: Converts a `NULL` value to `FALSE`.
- `array`: Converts an empty `array` to `FALSE`.
- `zero`: Converts a string to `FALSE` if it equates to `'0'` after type juggling.
- `string`: Converts an empty string `''` to `FALSE`.
- `float`: Converts a float `0.0` value to `FALSE`.
- `integer`: Converts an integer `0` value to `FALSE`.
- `boolean`: Returns a boolean value as is.

There are 2 additional special types:

- `all`: Converts all above types to `BOOLEAN`. The same as setting all above types.
- `php`: Converts all above types to `BOOLEAN` except `localized` or `false`. The same as setting all above types except `localized` or `false`.

All other given values will return `TRUE` by default.

There are several ways to select which of the above types are filtered. You can
give one or multiple types and add them, you can give an array, you can use
constants, or you can give a textual string.  See the following examples:

```php
// converts 0 to false
$filter = new Laminas\Filter\Boolean(Laminas\Filter\Boolean::TYPE_INTEGER);

// converts 0 and '0' to false
$filter = new Laminas\Filter\Boolean(
    Laminas\Filter\Boolean::TYPE_INTEGER + Laminas\Filter\Boolean::TYPE_ZERO_STRING
);

// converts 0 and '0' to false
$filter = new Laminas\Filter\Boolean([
    'type' => [
        Laminas\Filter\Boolean::TYPE_INTEGER,
        Laminas\Filter\Boolean::TYPE_ZERO_STRING,
    ],
]);

// converts 0 and '0' to false
$filter = new Laminas\Filter\Boolean([
    'type' => [
        'integer',
        'zero',
    ],
]);
```

You can also give an instance of `Laminas\Config\Config` to set the desired types.
To set types after instantiation, use the `setType()` method.

### Localized Booleans

As mentioned previously, `Laminas\Filter\Boolean` can also recognise localized "yes" and "no" strings.
This means that you can ask your customer in a form for "yes" or "no" within his native language and
`Laminas\Filter\Boolean` will convert the response to the appropriate boolean value.

To set the translation and the corresponding value, you can use the `translations` option or the
method `setTranslations`. The translations must be set for any values you wish to map to boolean values.

```php
$filter = new Laminas\Filter\Boolean([
    'type'         => Laminas\Filter\Boolean::TYPE_LOCALIZED,
    'translations' => [
        'ja'   => true,
        'nein' => false,
        'yes'  => true,
        'no'   => false,
    ],
]);

// returns false
$result = $filter->filter('nein');

// returns true
$result = $filter->filter('yes');
```

### Disable Casting

Sometimes it is necessary to recognise only `TRUE` or `FALSE` and return all
other values without changes. `Laminas\Filter\Boolean` allows you to do this by
setting the `casting` option to `FALSE`.

In this case `Laminas\Filter\Boolean` will work as described in the following
table, which shows which values return `TRUE` or `FALSE`. All other given values
are returned without change when `casting` is set to `FALSE`

Type Constant | Type String | True | False
---- | ---- | ---- | -----
`Laminas\Filter\Boolean::TYPE_BOOLEAN` | `boolean` | `TRUE` | `FALSE`
`Laminas\Filter\Boolean::TYPE_EMPTY_ARRAY` | `array` | | `[]`
`Laminas\Filter\Boolean::TYPE_FALSE_STRING` | `false` | `'true'` (case insensitive) | `'false'` (case insensitive)
`Laminas\Filter\Boolean::TYPE_FLOAT` | `float` | `1.0` | `0.0`
`Laminas\Filter\Boolean::TYPE_INTEGER` | `integer` | `1` | `0`
`Laminas\Filter\Boolean::TYPE_NULL` | `null` |  | `NULL`
`Laminas\Filter\Boolean::TYPE_STRING` | `string` | | `''`
`Laminas\Filter\Boolean::TYPE_ZERO_STRING` | `zero` | `'1'` | `'0'`

The following example shows the behavior when changing the `casting` option:

```php
$filter = new Laminas\Filter\Boolean([
    'type'    => Laminas\Filter\Boolean::TYPE_ALL,
    'casting' => false,
]);

// returns false
$result = $filter->filter(0);

// returns true
$result = $filter->filter(1);

// returns the value
$result = $filter->filter(2);
```

## Callback

This filter wraps any callable so that it can be invoked using the filter contract.

### Supported Options

The following options are supported for `Laminas\Filter\Callback`:

- `callback`: This sets the callback which should be used.
- `callback_params`: Optional. When provided this should be an array where each element of the array is an additional argument to your callback.

### Basic Usage

The following example uses PHP's built-in function `strrev` as the callback:

```php
$filter = new Laminas\Filter\Callback('strrev');

print $filter->filter('Hello!');
// returns "!olleH"
```

As previously mentioned, any type of callable can be used as a callback, for example a closure:

```php
$filter = new Laminas\Filter\Callback([
    'callback' => function (mixed $input): mixed {
        if (is_string($input)) {
            return $input . ' there!';
        }
        
        return $input;
    },
]);

$filter->filter('Hey'); // Returns 'Hey there!'
```

### Additional Callback Parameters

If your callback requires additional arguments, these can be passed as a list, or an associative array to the `callback_params` option. The first argument will be the value to be filtered.

```php
class MyClass {
    public function __invoke(mixed $input, string $a, string $b): string
    {
        if (is_string($input)) {
            return implode(', ', [$input, $a, $b]);
        }
        
        return 'Foo';
    }
}

$filter = new Laminas\Filter\Callback([
    'callback' => new MyClass(),
    'callback_params' => [
        'a' => 'baz',
        'b' => 'bat',
    ],
]);
$filter->filter('bing'); // returns 'bing,baz,bat'
```

## CompressString and DecompressString

These two filters compress and decompress strings using either `GZ` or `BZ2` compression algorithms.
Without any options, both filters will default to using `GZ` compression.

NOTE: **PHP Extensions Required**
These filters require either the [`zlib`](https://www.php.net/zlib) or [`bzip2`](https://www.php.net/bzip2) PHP extensions depending on the adapter in use.
Attempting to use these filters without the relevant extension installed will cause an exception to be thrown.

### Supported Options

The following options are supported for `Laminas\Filter\CompressString` and `Laminas\Filter\DecompressString`:

- `adapter`: The compression adapter which should be used. Defaults to `gz`.
  Supported values are: `gz` and `bz2`, or an instance of `Laminas\Filter\Compress\StringCompressionAdapterInterface`.

### Default Behaviour

By default, strings will be compressed using Gzip compression:

```php
$compressFilter = new Laminas\Filter\CompressString();
$compressed = $filter->filter('String Content');

$decompressFilter = new Laminas\Filter\DecompressString();
$value = $filter->filter($compressed); // "String Content"
```

Non-string input will pass through the filters unchanged.

When compression or decompression of string input fails, an exception will be thrown.

### Changing the Compression Type and Compression Level

Both filters have the option `adapter` that accepts the aliases `'bz2'` or `'gz'`:

```php
$filter = new Laminas\Filter\CompressString([
    'adapter' => 'bz2',
]);

$compressed = $filter->filter('Some Content');
```

The `level` option can be provided to either increase or decrease the compression factor at the expense of CPU time, and should be an integer between 1 and 9 with 9 providing the greatest level of compression.
Both `zlib` and `bzip2` extensions have a default compression level that balances compression ratio and performance; omitting the option uses these defaults.

```php
$filter = new Laminas\Filter\CompressString([
    'adapter' => 'bz2',
    'level' => 9,
]);

$compressed = $filter->filter('Some Content');
```

### Custom Compression Adapters

It is also possible to provide an instance of `Laminas\Filter\Compress\StringCompressionAdapterInterface` to the `adapter` option of either filter.
This can be leveraged to use compression algorithms other than BZ2 or Gzip.

## CompressToArchive

This filter compresses files, strings, directories and uploads to a pre-configured archive location using either `zip` or `tar` archive formats.
The default archive format is `Zip`.

NOTE: **Additional PHP Extensions or Dependencies Required**
These filters require either the [`zip`](https://www.php.net/zip) extension or the [`Archive_Tar` pear package](https://pear.php.net/package/Archive_Tar) depending on the adapter in use.
Attempting to use these filters without the relevant extension or dependency installed will cause an exception to be thrown.
`Archive_Tar` can be installed via composer with `composer require pear/archive_tar`.

### Supported Options

- `archive`: This is the destination archive. The option is required and must be a path to the target archive in a directory that exists, and is writable by PHP.
- `adapter`: Archive adapter - can be either `zip` or `tar` or an instance of `Laminas\Filter\Compress\ArchiveAdapterInterface`.
- `fileName`: When archiving arbitrary strings, the string will be placed in a file with this name prior to archiving.

### General Considerations

- If an archive already exists at the configured target archive, it will be overwritten.
- The filter will return the configured archive path for successfully filtered content.
- If the input cannot be filtered, the given value will be returned un-changed.

### Archiving a File Path

When the filter receives a file path, the file will be archived to the configured destination archive file:

```php
$filter = new Laminas\Filter\CompressToArchive([
    'archive' => '/path/to/archive.zip',
]);

$archiveLocation = $filter->filter('/path/to/any-file.txt');
```

### Archiving a Directory

Given a directory, the contents of that directory will be archived.

```php
$filter = new Laminas\Filter\CompressToArchive([
    'archive' => '/path/to/archive.zip',
]);

$archiveLocation = $filter->filter('/path/to/directory');
```

### Archiving Arbitrary Strings

If you wish to accept arbitrary string content, those contents will first be placed in a file with the file name configured in the *(required)* option `fileName` and archived in the configured archive location:

```php
$filter = new Laminas\Filter\CompressToArchive([
    'archive' => '/path/to/archive.zip',
    'fileName' => 'Content.txt',
]);

$filter->filter('Kermit the Frog');
```

### Archiving PSR7 Uploaded Files and PHP Uploads

When a PSR7 `UploadedFileInterface` or a `$_FILES` array is encountered, the uploaded file will be archived in the configured archive location:

```php
$filter = new Laminas\Filter\CompressToArchive([
    'archive' => '/path/to/archive.zip',
]);
$filter->filter($uploadedFile);
```

Note that PHP provides randomised filenames without a filename extension, so you may need to implement additional measures to track the contents of the archive as something more meaningful.
This filter does not use the "Client File Name" provided as part of the uploaded file information because it can't be guaranteed to be sanitised prior to filtering.

### Using a Different Adapter

2 adapters are available: `zip` and `tar`.
You can specify which adapter to use with the `adapter` option, and this option can also be any object that implements `Laminas\Filter\Compress\ArchiveAdapterInterface`:

```php
$filter = new Laminas\Filter\CompressToArchive([
    'archive' => '/path/to/archive.tar',
    'adapter' => 'tar',
]);

$archiveLocation = $filter->filter('/path/to/file.txt');
```

## DateSelect

`Laminas\Filter\DateSelect` allows you to filter a day, month, and year value into a dash separated string.

### Supported Options

The following options are supported for `Laminas\Filter\DateSelect`:

- `null_on_empty` => This defaults to `false`.
If set to `true`, the filter will return `null` if day, month, or year are empty.
- `null_on_all_empty` => This defaults to `false`.
If set to `true`, the filter will return `null` if day, month, and year are empty.

### Basic Usage

```php
$filter = new Laminas\Filter\DateSelect();

print $filter->filter(['day' => '1', 'month' => '2', 'year' => '2012']);
````

This will return '2012-02-01'.

## DateTimeFormatter

This filter formats either a `DateTimeInterface` object, a string, or integer that `DateTime` will understand to a date
and/or time string in the configured format.

### Supported Options

The following options are supported for `Laminas\Filter\DateTimeFormatter`

- `format`: a valid [date format](https://www.php.net/manual/datetime.format.php) to use when formatting a string, for example `l jS F Y`. This option defaults to `DateTimeInterface::ATOM` when unspecified
- `timezone` : The default timezone to apply when converting a string or integer argument to a `DateTime` instance. This option falls back to the system timezone when unspecified

### Basic Usage

#### Without Any Options

```php
$filter = new \Laminas\Filter\DateTimeFormatter();

echo $filter->filter('2024-01-01'); // => 2024-01-01T00:00:00+00:00
echo $filter->filter(1_359_739_801); // => 2013-02-01T17:30:01+00:00
echo $filter->filter(new DateTimeImmutable('2024-01-01')) // => 2024-01-01T00:00:00+00:00 
```

#### With `format` Option

```php
$filter = new \Laminas\Filter\DateTimeFormatter([
    'format' => 'd-m-Y'
]);
echo $filter->filter('2024-08-16 00:00:00'); // => 16-08-2024
```

#### With `timezone` Option

```php
$filter = new \Laminas\Filter\DateTimeFormatter([
    'timezone' => 'Europe/Paris'
]);
echo $filter->filter('2024-01-01'); // => 2024-01-01T00:00:00+01:00
```

## DateTimeSelect

`Laminas\Filter\DateTimeSelect` allows you to filter second, minute, hour, day, month, and year values into a string of format `Y-m-d H:i:s`.
If not in the input array, second will default to 0.

### Supported Options

The following options are supported for `Laminas\Filter\DateTimeSelect`:

- `null_on_empty` => This defaults to `false`.
  If set to `true`, the filter will return `null` if minute, hour, day, month, or year are empty.
- `null_on_all_empty` => This defaults to `false`.
  If set to `true`, the filter will return `null` if minute, hour, day, month, and year are empty.

### Basic Usage

```php
$filter = new Laminas\Filter\DateTimeSelect();

print $filter->filter(['second' => '1', 'month' => '2', 'hour' => '3', 'day' => '4', 'month' => '5', 'year' => '2012']);
```

This will return '2012-05-04 03:02:01'.

## DecompressArchive

This filter accepts an archive in the form of a file path, a PHP uploaded file array or a PSR-7 uploaded file and de-compresses the file to a configured target directory returning the location where the files are expanded.

NOTE: **Additional PHP Extensions or Dependencies Required**
These filters require either the [`zip`](https://www.php.net/zip) extension or the [`Archive_Tar` pear package](https://pear.php.net/package/Archive_Tar) depending on the adapter in use.
Attempting to use these filters without the relevant extension or dependency installed will cause an exception to be thrown.
`Archive_Tar` can be installed via composer with `composer require pear/archive_tar`.

### Supported Options

- `target` *(required)* A path to the directory where files will be expanded
- `matcher` *(optional)* An instance of `Laminas\Compress\ArchiveAdapterResolverInterface` used to determine the appropriate adapter to use for the detected file type.

### Basic Behaviour

```php
$filter = new Laminas\Filter\DecompressArchive([
    'target' => '/path/to/writable/directory',
]);

$result = $filter->filter('/path/to/an-archive.tar.gz');
assert($result === '/path/to/writable/directory');
```

NOTE: **The target directory must exist** The directory configured for expanding files must exist, *and* it must be writable. The filter makes no attempt to create intermediate directories.

When the input cannot be recognised as a supported archive type, or the input cannot be filtered for any other reason, the input is returned un-altered:

```php
$filter = new Laminas\Filter\DecompressArchive([
    'target' => '/path/to/writable/directory',
]);

$directory = $filter->filter('Fozzy Bear');
assert($result === 'Fozzy Bear');
```

### Handling Different Archive Types

The type of archive will be automatically detected, first by using PHP's built-in mime-type detection *(via mime magic)* and falling back to filename extension, then, the relevant archive adapter will then be used to expand the archive.

Zip and Tar archives are supported out of the box.

Other archive formats can be supported by writing custom adapters and configuring or creating custom matchers to map mime-type or filename extensions to the custom adapter.

```php
$filter = new Laminas\Filter\DecompressArchive([
    'target' => '/path/to/extract/to',
    'matcher' => new MyCustomArchiveAdapterResolver(),
]);
```

### Security Considerations

There is no protection from [Zip Bombs](https://wikipedia.org/wiki/Zip_bomb) in this filter. It is your responsibility to validate and sanitize the input prior to applying this filter.

## DenyList

This filter will return `null` if the value being filtered is present in the filter's list of
values. If the value is not present, it will return that value.

For the opposite functionality, see the [`AllowList` filter](#allowlist).

### Supported Options

The following options are supported for `Laminas\Filter\DenyList`:

- `strict`: Uses strict mode when comparing; passed to `in_array()`'s third argument.
- `list`: An array of forbidden values.

### Basic Usage

```php
$denyList = new \Laminas\Filter\DenyList([
    'list' => ['forbidden-1', 'forbidden-2']
]);
echo $denyList->filter('forbidden-1'); // => null
echo $denyList->filter('allowed');     // => 'allowed'
```

## Digits

Returns the string `$value`, removing all but digits.

### Supported Options

There are no additional options for `Laminas\Filter\Digits`.

### Basic Usage

```php
$filter = new Laminas\Filter\Digits();

print $filter->filter('October 2012');
```

This returns "2012".

```php
$filter = new Laminas\Filter\Digits();

print $filter->filter('HTML 5 for Dummies');
```

This returns "5".

## Dir

Given a string containing a path to a file, this function will return the name of the directory.

### Supported Options

There are no additional options for `Laminas\Filter\Dir`.

### Basic Usage

```php
$filter = new Laminas\Filter\Dir();

print $filter->filter('/etc/passwd');
```

This returns `/etc`.

```php
$filter = new Laminas\Filter\Dir();

print $filter->filter('C:/Temp/x');
```

This returns `C:/Temp`.

## HtmlEntities

Returns the string `$value`, converting characters to their corresponding HTML
entity equivalents when possible.

### Supported Options

The following options are supported for `Laminas\Filter\HtmlEntities`:

- `quotestyle`: Equivalent to the PHP `htmlentities()` native function parameter
  `flags`.  This allows you to define what will be done with 'single' and
  "double" quotes. The following constants are accepted: `ENT_COMPAT`,
  `ENT_QUOTES`, and `ENT_NOQUOTES`, with the default being `ENT_COMPAT`.
- `encoding`: Equivalent to the PHP `htmlentities()` native function parameter
  `encoding`. This defines the character set to be used in filtering. Unlike the
  PHP native function, the default is 'UTF-8'. See the [PHP htmlentities
  manual](http://php.net/htmlentities) for a list of supported character sets.
- `doublequote`: Equivalent to the PHP `htmlentities()` native function
  parameter `double_encode`. If set to `false`, existing HTML entities will not
  be encoded. The default is to convert everything (`true`).

### Basic Usage

```php
$filter = new Laminas\Filter\HtmlEntities();

print $filter->filter('<');
```

### Quote Style

`Laminas\Filter\HtmlEntities` allows changing the quote style used. This can be useful when you want to
leave double, single, or both types of quotes un-filtered.

```php
$filter = new Laminas\Filter\HtmlEntities(['quotestyle' => ENT_QUOTES]);

$input = "A 'single' and " . '"double"';
print $filter->filter($input);
```

The above example returns `A &#039;single&#039; and &quot;double&quot;`. Notice
that 'single' as well as "double" quotes are filtered.

```php
$filter = new Laminas\Filter\HtmlEntities(['quotestyle' => ENT_COMPAT]);

$input = "A 'single' and " . '"double"';
print $filter->filter($input);
```

The above example returns `A 'single' and &quot;double&quot;`. Notice that
"double" quotes are filtered while 'single' quotes are not altered.

```php
$filter = new Laminas\Filter\HtmlEntities(['quotestyle' => ENT_NOQUOTES]);

$input = "A 'single' and " . '"double"';
print $filter->filter($input);
```

The above example returns `A 'single' and "double"`. Notice that neither
"double" or 'single' quotes are altered.

## ToFloat

`Laminas\Filter\ToFloat` allows you to transform a scalar value into a float.

### Supported Options

There are no additional options for `Laminas\Filter\ToFloat`.

### Basic Usage

```php
$filter = new Laminas\Filter\ToFloat();

print $filter->filter('-4.4');
```

This will return `-4.4` (as a float).

## MonthSelect

`Laminas\Filter\MonthSelect` allows you to filter a month and year value into a dash separated string.

### Supported Options

The following options are supported for `Laminas\Filter\MonthSelect`:

- `null_on_empty` => This defaults to `false`.
If set to `true`, the filter will return `null` if either month or year is empty.
- `null_on_all_empty` => This defaults to `false`.
If set to `true`, the filter will return `null` if both month and year are empty.

### Basic Usage

```php
$filter = new Laminas\Filter\MonthSelect();

print $filter->filter(['month' => '2', 'year' => '2012']);
````

This will return '2012-02'.

## ToEnum

`Laminas\Filter\ToEnum` transforms strings and integers to unit or backed enum instances.

### Supported Options

This filter requires a single option `enum` which must be a class-string representing a PHP enum.

### Basic Usage

```php
enum Muppets: string {
    case Kermit = 'Kermit';
    case MissPiggy = 'Miss Piggy';
}

$filter = new Laminas\Filter\ToEnum([
    'enum' => Muppets::class,
]);

$enum = $filter->filter('Kermit'); // Enum Instance
$enum = $filter->filter('Miss Piggy'); // Enum Instance
$enum = $filter->filter('MissPiggy'); // Enum Instance
$failed = $filter->filter('Not There'); // "Not There"
```

To successfully filter to a Unit enum, the case name is expected.
The case name can also be used for both int and string backed enums, as well as a matching value.

## ToInt

`Laminas\Filter\ToInt` allows you to transform a scalar value into an integer.

### Supported Options

There are no additional options for `Laminas\Filter\ToInt`.

### Basic Usage

```php
$filter = new Laminas\Filter\ToInt();

print $filter->filter('-4 is less than 0');
```

This will return '-4'.

## ToNull

This filter will change the given input to be `NULL` if it meets specific criteria.
This is often necessary when you work with databases and want to have a `NULL` value instead of a boolean or any other type.

### Supported Options

The following options are supported for `Laminas\Filter\ToNull`:

- `type`: The variable type which should be supported.

### Default Behavior

Per default this filter works like PHP's `empty()` method;
in other words, if `empty()` returns a boolean `TRUE`, then a `NULL` value will be returned.

```php
$filter = new Laminas\Filter\ToNull();
$value  = '';
$result = $filter->filter($value);
// returns null instead of the empty string
```

This means that without providing any configuration, `Laminas\Filter\ToNull` will accept all input types and return `NULL` in the same cases as `empty()`.

Any other value will be returned as is, without any changes.

### Changing the Default Behavior

Sometimes it's not enough to filter based on `empty()`.
Therefore `Laminas\Filter\ToNull` allows you to configure which types will be converted, and which not.

The following types can be handled:

- `boolean`: Converts a boolean `FALSE` value to `NULL`.
- `integer`: Converts an integer `0` value to `NULL`.
- `array`: Converts an empty `array` to `NULL`.
- `float`: Converts a float `0.0` value to `NULL`.
- `string`: Converts an empty string `''` to `NULL`.
- `zero`: Converts a string containing the single character zero (`'0'`) to `NULL`.
- `all`: Converts all above types to `NULL`. (This is the default behavior.)

There are several ways to select which of the above types are filtered.
You can give one or multiple types and add them, you can give an array, you can use constants, or you can give a textual string.

See the following examples:

```php
// converts false to null
$filter = new Laminas\Filter\ToNull([
    'type' => Laminas\Filter\ToNull::BOOLEAN,
]);

// converts false and 0 to null
$filter = new Laminas\Filter\ToNull([
    'type' => Laminas\Filter\ToNull::BOOLEAN | Laminas\Filter\ToNull::INTEGER
]);

// converts false and 0 to null
$filter = new Laminas\Filter\ToNull([
    'type' => [
        Laminas\Filter\ToNull::BOOLEAN,
        Laminas\Filter\ToNull::INTEGER
    ],
]);

// converts false and 0 to null
$filter = new Laminas\Filter\ToNull([
    'type' => [
        'boolean',
        'integer',
    ],
]);

// converts only empty arrays to null
$filter = new Laminas\Filter\ToNull([
    'type' => 'array',
]);
```

It is best practice is to use the `TYPE_*` constants rather than the human-readable strings. Modern IDEs will autocomplete these for you and usage and refactoring is easier.

## ToString

The `ToString` filter casts `Stringable` objects or scalar values to `string`.
If an array is received, member values are cast to a string recursively.

This filter has no runtime options.

### Basic Usage

```php
$filter = new \Laminas\Filter\ToString();

$filter->filter(123); // "123"
$filter->filter(['id' => 100, 'roles' => null]); // ['id' => "100", 'roles' => null]
```

Non-scalar or null input will be returned un-filtered:

```php
$filter = new \Laminas\Filter\ToString();

$filter->filter(null); // null
$filter->filter(new \stdClass()); // \stdClass object
```

## PregReplace

`Laminas\Filter\PregReplace` performs a search using regular expressions and replaces all found elements.

### Supported Options

The following options are supported for `Laminas\Filter\PregReplace`:

- `pattern`: The pattern to search for.
- `replacement`: The string which to use as a replacement for the matches; this can optionally contain placeholders for matched groups in the search pattern.

### Basic Usage

To use this filter properly, you must give both options listed above.

The `pattern` option has to be given to set the pattern to search for.
It can be a string for a single pattern, or an array of strings for multiple patterns.

The `replacement` option indicates the string to replace matches with, and can contain placeholders for matched groups from the search `pattern`.
The value may be a string replacement, or an array of string replacements.

```php
$filter = new Laminas\Filter\PregReplace([
    'pattern'     => '/bob/',
    'replacement' => 'john',
]);
$input  = 'Hi bob!';

$filter->filter($input);
// returns 'Hi john!'
```

For more complex usage, read the
[PCRE Pattern chapter of the PHP manual](http://www.php.net/manual/reference.pcre.pattern.modifiers.php).

## RealPath

This filter will resolve given links and pathnames, and returns the canonicalized absolute pathnames.

### Supported Options

The following options are supported for `Laminas\Filter\RealPath`:

- `exists`: This option defaults to `TRUE`, which validates that the given path
  really exists.

### Basic Usage

For any given link or pathname, its absolute path will be returned.
References to `/./`, `/../` and extra `/` sequences in the input path will be stripped.
The resulting path will not have any symbolic links, `/./`, or `/../` sequences.

`Laminas\Filter\RealPath` will return the value passed to the filter on failure, e.g. if the file does not exist.
On BSD systems `Laminas\Filter\RealPath` doesn't fail if only the last path component doesn't exist, while other systems will return the value passed to the filter.

```php
$filter = new Laminas\Filter\RealPath();
$path = '/www/var/path/../../mypath';
$filtered = $filter->filter($path);

// returns '/www/mypath'
```

### Non-Existing Paths

Sometimes it is useful to get paths to files that do not exist; e.g., when you want to get the real path for a path you want to create.
You can then provide `false` for the `exists` option during construction.

```php
$filter = new Laminas\Filter\RealPath(['exists' => false]);
$path = '/www/var/path/../../non/existing/path';
$filtered = $filter->filter($path);

// returns '/www/non/existing/path'
// even when file_exists or realpath would return false
```

## StringPrefix

This filter will add the provided prefix to scalar values or scalar array members.

### Supported Options

The following options are supported for `Laminas\Filter\StringPrefix`:

- `prefix`: The string prefix to add to values.

### Basic Usage

```php
$filter = new Laminas\Filter\StringPrefix([
    'prefix' => 'PHP-',
]);

echo $filter->filter('MidCentral'); // "PHP-MidCentral"

$array = $filter->filter(['East', 'West']); // ['PHP-East', 'PHP-West']
```

## StringSuffix

This filter will add the provided suffix to scalar values or scalar array members.

### Supported Options

The following options are supported for `Laminas\Filter\StringSuffix`:

- `suffix`: The string suffix to append to values.

### Basic Usage

```php
$filter = new Laminas\Filter\StringSuffix([
    'suffix' => '-PHP',
]);

echo $filter->filter('MidCentral'); // "MidCentral-PHP"

$array = $filter->filter(['East', 'West']); // ['East-PHP', 'West-PHP']
```

## StringToLower

This filter converts string input to lowercase.

### Supported Options

The following options are supported for `Laminas\Filter\StringToLower`:

- `encoding`: This option can be used to set the expected character encoding of the input.

### Basic Usage

```php
$filter = new Laminas\Filter\StringToLower();

print $filter->filter('SAMPLE');
// returns "sample"
```

### Handling Alternate Encodings

By default, `StringToLower` will only handle characters from the locale of your server; characters from other charsets will be ignored.
To correctly filter input in encodings other than the default detected encoding for your environment, pass the
desired encoding when initiating the `StringToLower` filter.

```php
$filter = new Laminas\Filter\StringToLower([
    'encoding' => 'UTF-8',
]);
```

NOTE: **Setting invalid Encodings**
Be aware that you will get an exception when you provide an encoding that is not supported by the `mbstring` extension.

## StringToUpper

This filter converts string input to UPPERCASE.

### Supported Options

The following options are supported for `Laminas\Filter\StringToUpper`:

- `encoding`: This option can be used to set the expected character encoding of the input.

### Basic Usage

```php
$filter = new Laminas\Filter\StringToUpper();

print $filter->filter('Sample');
// returns "SAMPLE"
```

### Handling Alternate Encodings

By default, `StringToUpper` will only handle characters from the locale of your server; characters from other charsets will be ignored.
To correctly filter input in encodings other than the default detected encoding for your environment, pass the
desired encoding when initiating the `StringToUpper` filter.

```php
$filter = new Laminas\Filter\StringToUpper([
    'encoding' => 'UTF-8',
]);
```

## StringTrim

This filter modifies a given string such that certain characters are removed
from the beginning and end.

### Supported Options

The following options are supported for `Laminas\Filter\StringTrim`:

- `charlist`: List of characters to remove from the beginning and end of the
  string. If this is not set or is null, the default behavior will be invoked,
  which is to remove only whitespace from the beginning and end of the string.

### Basic Usage

```php
$filter = new Laminas\Filter\StringTrim();

print $filter->filter(' This is (my) content: ');
```

The above example returns `This is (my) content:`. Notice that the whitespace
characters have been removed.

### Specifying alternate Characters

```php
$filter = new Laminas\Filter\StringTrim(['charlist' => ':']);

print $filter->filter(' This is (my) content:');
```

<!-- markdownlint-disable-next-line no-space-in-code -->
The above example returns ` This is (my) content`. Notice that only the colon is removed.

## StripNewlines

This filter modifies a given string and removes all new line characters within that string.

When provided with an array, all *scalar* elements of the array will be cast to string and have new line characters removed.
The operation is also recursive, so nested arrays will be processed in the same way.

### Supported Options

There are no additional options for `Laminas\Filter\StripNewlines`:

### Basic Usage

```php
$filter = new Laminas\Filter\StripNewlines();

print $filter->filter(' This is (my)``\n\r``content: ');
```

The above example returns `This is (my) content:`. Notice that all newline
characters have been removed.

## StripTags

This filter can strip XML and HTML tags from given content.

> WARNING: **This filter is potentially insecure**
>
> Be warned that `Laminas\Filter\StripTags` should only be used to strip *all* available tags.
> Using `Laminas\Filter\StripTags` to make your site secure by stripping *some* unwanted tags will lead to unsecure and dangerous code, including potential XSS vectors.
>
> For a fully secure solution that allows selected filtering of HTML tags, use either Tidy or HtmlPurifier.

### Supported Options

The following options are supported for `Laminas\Filter\StripTags`:

- `allowAttribs`: This option sets the attributes which are accepted. All other
  attributes are stripped from the given content.
- `allowTags`: This option sets the tags which are accepted. All other tags will
  be stripped from; the given content.

### Basic Usage

```php
$filter = new Laminas\Filter\StripTags();

print $filter->filter('<B>My content</B>');
```

The result will be the stripped content `My content`.

When the content contains broken or partial tags, any content following the
opening tag will be completely removed:

```php
$filter = new Laminas\Filter\StripTags();

print $filter->filter('This contains <a href="http://example.com">no ending tag');
```

The above will return `This contains`, with the rest being stripped.

### Allowing defined Tags

`Laminas\Filter\StripTags` allows stripping all but an allowed set of tags. As an
example, this can be used to strip all markup except for links:

```php
$filter = new Laminas\Filter\StripTags(['allowTags' => ['a']]);

$input  = "A text with <br/> a <a href='link.com'>link</a>";
print $filter->filter($input);
```

The above will return `A text with a <a href='link.com'>link</a>`;
it strips all tags but the link. By providing an array, you can specify multiple
tags at once.

WARNING: **Warning**
Do not use this feature to secure content.
This component does not replace the use of a properly configured html filter.

### Allowing defined Attributes

You can also strip all but an allowed set of attributes from a tag:

```php
$filter = new Laminas\Filter\StripTags([
    'allowTags' => ['img'],
    'allowAttribs' => ['src'],
]);

$input  = "A text with <br/> a <img src='picture.com' width='100'>picture</img>";
print $filter->filter($input);
```

The above will return `A text with a <img src='picture.com'>picture</img>`; it
strips all tags but `<img>`, and all attributes but `src` from those tags.By
providing an array you can set multiple attributes at once.

### Allow specific Tags with specific Attributes

You can also pass the tag allow list as a set of tag/attribute values. Each key
will be an allowed tag, pointing to a list of allowed attributes for that
tag.

```php
$filter = new Laminas\Filter\StripTags([
    'allowTags' => [
        'img' => [
            'src',
            'width'
        ],
        'a' => [
            'href'
        ]
    ]
]);

$input = "A text with <br/> a <img src='picture.com' width='100'>picture</img> click "
    . "<a href='http://picture.com/laminas' id='hereId'>here</a>!";
print $filter->filter($input);
```

The above will return
`A text with a <img src='picture.com' width='100'>picture</img> click <a href='<http://picture.com/laminas>'>here</a>!`
as the result.
