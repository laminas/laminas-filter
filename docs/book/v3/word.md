# Word Filters

In addition to the standard set of filters, there are several classes specific
to filtering word strings.

## CamelCaseToDash

TIP: **New Behaviour since Version 3**
The filter will now treat numbers as a word boundary.
For example `ThisHas4Words` will filter to `This-Has-4-Words`.

This filter modifies a given string such that `CamelCaseWords` are converted to `Camel-Case-Words`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\CamelCaseToDash();

print $filter->filter('ThisIsMyContent');
```

The above example returns `This-Is-My-Content`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\CamelCaseToDash`.

## CamelCaseToSeparator

TIP: **New Behaviour since Version 3**
The filter will now treat numbers as a word boundary.
For example `ThisHas4Words` with the default separator will filter to `This Has 4 Words`

This filter modifies a given string such that `CamelCaseWords` are converted to `Camel Case Words`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\CamelCaseToSeparator();

print $filter->filter('ThisIsMyContent');
```

The above example returns `This Is My Content`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\CamelCaseToSeparator`:

| Option      | Description           | Type     | Default     |
|-------------|-----------------------|----------|-------------|
| `separator` | A separator character | `string` | ' ' (space) |

#### Example

```php
$filter = new Laminas\Filter\Word\CamelCaseToSeparator(['separator' => ':']);

print $filter->filter('ThisIsMyContent');
```

The above example returns `This:Is:My:Content`.

## CamelCaseToUnderscore

TIP: **New Behaviour since Version 3**
The filter will now treat numbers as a word boundary.
For example `ThisHas4Words` will filter to `This_Has_4_Words`

This filter modifies a given string such that `CamelCaseWords` are converted to
`Camel_Case_Words`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\CamelCaseToUnderscore();

print $filter->filter('ThisIsMyContent');
```

The above example returns `This_Is_My_Content`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\CamelCaseToUnderscore`.

## DashToCamelCase

This filter modifies a given string such that `words-with-dashes` are converted
to `WordsWithDashes`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\DashToCamelCase();

print $filter->filter('this-is-my-content');
```

The above example returns `ThisIsMyContent`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\DashToCamelCase`:

## DashToSeparator

This filter modifies a given string such that `words-with-dashes` are converted
to `words with dashes`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\DashToSeparator();

print $filter->filter('this-is-my-content');
```

The above example returns `this is my content`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\DashToSeparator`:

| Option      | Description           | Type     | Default     |
|-------------|-----------------------|----------|-------------|
| `separator` | A separator character | `string` | ' ' (space) |

#### Example

```php
$filter = new Laminas\Filter\Word\DashToSeparator(['separator' => '+']);

print $filter->filter('this-is-my-content');
```

The above example returns `this+is+my+content`.

## DashToUnderscore

This filter modifies a given string such that `words-with-dashes` are converted
to `words_with_dashes`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\DashToUnderscore();

print $filter->filter('this-is-my-content');
```

The above example returns `this_is_my_content`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\DashToUnderscore`.

## SeparatorToCamelCase

This filter modifies a given string such that `words with separators` are
converted to `WordsWithSeparators`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\SeparatorToCamelCase();

print $filter->filter('this is my content');
```

The above example returns `ThisIsMyContent`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\SeparatorToCamelCase`:

| Option      | Description           | Type     | Default     |
|-------------|-----------------------|----------|-------------|
| `separator` | A separator character | `string` | ' ' (space) |

#### Example

```php
$filter = new Laminas\Filter\Word\SeparatorToCamelCase(['separator' => ':']);

print $filter->filter('this:is:my:content');
```

The above example returns `ThisIsMyContent`.

## SeparatorToDash

This filter modifies a given string such that `words with separators` are
converted to `words-with-separators`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\SeparatorToDash();

print $filter->filter('this is my content');
```

The above example returns `this-is-my-content`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\SeparatorToDash`:

| Option      | Description            | Type     | Default     |
|-------------|------------------------|----------|-------------|
| `separator` | A separator character. | `string` | ' ' (space) |

#### Example

```php
$filter = new Laminas\Filter\Word\SeparatorToDash(['separator' => ':']);

print $filter->filter('this:is:my:content');
```

The above example returns `this-is-my-content`.

## SeparatorToSeparator

This filter modifies a given string such that `words with separators` are
converted to `words-with-separators`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\SeparatorToSeparator();

print $filter->filter('this is my content');
```

The above example returns `this-is-my-content`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\SeparatorToSeparator`:

| Option             | Description                         | Type     | Default     |
|--------------------|-------------------------------------|----------|-------------|
| `searchSeparator`  | The search separator character      | `string` | ' ' (space) |
| `replaceSeparator` | The replacement separator character | `string` | `-`         |

#### Example

```php
$filter = new Laminas\Filter\Word\SeparatorToSeparator([
    'search_separator'      => ':',
    'replacement_separator' => '+',
]);

print $filter->filter('this:is:my:content');
```

The above example returns `this+is+my+content`.

## UnderscoreToCamelCase

This filter modifies a given string such that `words_with_underscores` are
converted to `WordsWithUnderscores`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToCamelCase();

print $filter->filter('this_is_my_content');
```

The above example returns `ThisIsMyContent`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\UnderscoreToCamelCase`.

## UnderscoreToSeparator

This filter modifies a given string such that `words_with_underscores` are
converted to `words with underscores`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToSeparator();

print $filter->filter('this_is_my_content');
```

The above example returns `this is my content`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\UnderscoreToSeparator`:

| Option      | Description           | Type     | Default     |
|-------------|-----------------------|----------|-------------|
| `separator` | A separator character | `string` | ' ' (space) |

#### Example

```php
$filter = new Laminas\Filter\Word\UnderscoreToSeparator(['separator' => '+']));

print $filter->filter('this_is_my_content');
```

The above example returns `this+is+my+content`.

## UnderscoreToDash

This filter modifies a given string such that `words_with_underscores` are
converted to `words-with-underscores`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToDash();

print $filter->filter('this_is_my_content');
```

The above example returns `this-is-my-content`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\UnderscoreToDash`.

## UnderscoreToStudlyCase

This filter modifies a given string such that `words_with_underscores` are
converted to `wordsWithUnderscores`.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToStudlyCase();

print $filter->filter('this_is_my_content');
```

The above example returns `thisIsMyContent`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\UnderscoreToStudlyCase`.
