# Word Filters

In addition to the standard set of filters, there are several classes specific
to filtering word strings.

## CamelCaseToDash

This filter modifies a given string such that `CamelCaseWords` are converted to `Camel-Case-Words`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\CamelCaseToDash`:

### Basic Usage

```php
$filter = new Laminas\Filter\Word\CamelCaseToDash();

print $filter->filter('ThisIsMyContent');
```

The above example returns `This-Is-My-Content`.

## CamelCaseToSeparator

This filter modifies a given string such that `CamelCaseWords` are converted to `Camel Case Words`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\CamelCaseToSeparator`:

- `separator`: A separator character. If this is not set, the default separator
  is a space.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\CamelCaseToSeparator(':');
// or new Laminas\Filter\Word\CamelCaseToSeparator(array('separator' => ':'));

print $filter->filter('ThisIsMyContent');
```

The above example returns `This:Is:My:Content`.

### Default Behavior

```php
$filter = new Laminas\Filter\Word\CamelCaseToSeparator();

print $filter->filter('ThisIsMyContent');
```

The above example returns `This Is My Content`.

## CamelCaseToUnderscore

This filter modifies a given string such that `CamelCaseWords` are converted to
`Camel_Case_Words`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\CamelCaseToUnderscore`:

### Basic Usage

```php
$filter = new Laminas\Filter\Word\CamelCaseToUnderscore();

print $filter->filter('ThisIsMyContent');
```

The above example returns `This_Is_My_Content`.

## DashToCamelCase

This filter modifies a given string such that `words-with-dashes` are converted
to `WordsWithDashes`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\DashToCamelCase`:

### Basic Usage

```php
$filter = new Laminas\Filter\Word\DashToCamelCase();

print $filter->filter('this-is-my-content');
```

The above example returns `ThisIsMyContent`.

## DashToSeparator

This filter modifies a given string such that `words-with-dashes` are converted
to `words with dashes`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\DashToSeparator`:

- `separator`: A separator character. If this is not set, the default separator
  is a space.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\DashToSeparator(['separator' => '+']);

print $filter->filter('this-is-my-content');
```

The above example returns `this+is+my+content`.

### Default Behavior

```php
$filter = new Laminas\Filter\Word\DashToSeparator();

print $filter->filter('this-is-my-content');
```

The above example returns `this is my content`.

## DashToUnderscore

This filter modifies a given string such that `words-with-dashes` are converted
to `words_with_dashes`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\DashToUnderscore`:

### Basic Usage

```php
$filter = new Laminas\Filter\Word\DashToUnderscore();

print $filter->filter('this-is-my-content');
```

The above example returns `this_is_my_content`.

## SeparatorToCamelCase

This filter modifies a given string such that `words with separators` are
converted to `WordsWithSeparators`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\SeparatorToCamelCase`:

- `separator`: A separator character. If this is not set, the default separator
  is a space.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\SeparatorToCamelCase(':');
// or new Laminas\Filter\Word\SeparatorToCamelCase(array('separator' => ':'));

print $filter->filter('this:is:my:content');
```

The above example returns `ThisIsMyContent`.

### Default Behavior

```php
$filter = new Laminas\Filter\Word\SeparatorToCamelCase();

print $filter->filter('this is my content');
```

The above example returns `ThisIsMyContent`.

## SeparatorToDash

This filter modifies a given string such that `words with separators` are
converted to `words-with-separators`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\SeparatorToDash`:

- `separator`: A separator character. If this is not set, the default separator
  is a space.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\SeparatorToDash(':');
// or new Laminas\Filter\Word\SeparatorToDash(array('separator' => ':'));

print $filter->filter('this:is:my:content');
```

The above example returns `this-is-my-content`.

### Default Behavior

```php
$filter = new Laminas\Filter\Word\SeparatorToDash();

print $filter->filter('this is my content');
```

The above example returns `this-is-my-content`.

## SeparatorToSeparator

This filter modifies a given string such that `words with separators` are
converted to `words-with-separators`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\SeparatorToSeparator`:

- `searchSeparator`: The search separator character. If this is not set, the
  default separator is a space.
- `replaceSeparator`: The replacement separator character. If this is not set, the
  default separator is a dash (`-`).

### Basic Usage

```php
$filter = new Laminas\Filter\Word\SeparatorToSeparator(':', '+');

print $filter->filter('this:is:my:content');
```

The above example returns `this+is+my+content`.

### Default Behaviour

```php
$filter = new Laminas\Filter\Word\SeparatorToSeparator();

print $filter->filter('this is my content');
```

The above example returns `this-is-my-content`.

## UnderscoreToCamelCase

This filter modifies a given string such that `words_with_underscores` are
converted to `WordsWithUnderscores`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\UnderscoreToCamelCase`:

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToCamelCase();

print $filter->filter('this_is_my_content');
```

The above example returns `ThisIsMyContent`.

## UnderscoreToSeparator

This filter modifies a given string such that `words_with_underscores` are
converted to `words with underscores`.

### Supported Options

The following options are supported for `Laminas\Filter\Word\UnderscoreToSeparator`:

- `separator`: A separator character. If this is not set, the default separator
  is a space.

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToSeparator('+');
// or new Laminas\Filter\Word\CamelCaseToSeparator(array('separator' => '+'));

print $filter->filter('this_is_my_content');
```

The above example returns `this+is+my+content`.

### Default Behavior

```php
$filter = new Laminas\Filter\Word\UnderscoreToSeparator();

print $filter->filter('this_is_my_content');
```

The above example returns `this is my content`.

## UnderscoreToDash

This filter modifies a given string such that `words_with_underscores` are
converted to `words-with-underscores`.

### Supported Options

There are no additional options for `Laminas\Filter\Word\UnderscoreToDash`:

### Basic Usage

```php
$filter = new Laminas\Filter\Word\UnderscoreToDash();

print $filter->filter('this_is_my_content');
```

The above example returns `this-is-my-content`.
