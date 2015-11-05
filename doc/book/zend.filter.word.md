# Word Filters

In addition to the standard set of filters, there are several classes specific to filtering word
strings.

## CamelCaseToDash

This filter modifies a given string such that 'CamelCaseWords' are converted to 'Camel-Case-Words'.

### Supported Options

There are no additional options for `Zend\Filter\Word\CamelCaseToDash`:

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\CamelCaseToDash();

print $filter->filter('ThisIsMyContent');
```

The above example returns 'This-Is-My-Content'.

## CamelCaseToSeparator

This filter modifies a given string such that 'CamelCaseWords' are converted to 'Camel Case Words'.

### Supported Options

The following options are supported for `Zend\Filter\Word\CamelCaseToSeparator`:

- **separator**: A separator char. If this is not set the separator will be a space character.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\CamelCaseToSeparator(':');
// or new Zend\Filter\Word\CamelCaseToSeparator(array('separator' => ':'));

print $filter->filter('ThisIsMyContent');
```

The above example returns 'This:Is:My:Content'.

### Default Behavior

```php
$filter = new Zend\Filter\Word\CamelCaseToSeparator();

print $filter->filter('ThisIsMyContent');
```

The above example returns 'This Is My Content'.

## CamelCaseToUnderscore

This filter modifies a given string such that 'CamelCaseWords' are converted to
'Camel\_Case\_Words'.

### Supported Options

There are no additional options for `Zend\Filter\Word\CamelCaseToUnderscore`:

### Basic usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\CamelCaseToUnderscore();

print $filter->filter('ThisIsMyContent');
```

The above example returns 'This\_Is\_My\_Content'.

## DashToCamelCase

This filter modifies a given string such that 'words-with-dashes' are converted to
'WordsWithDashes'.

### Supported Options

There are no additional options for `Zend\Filter\Word\DashToCamelCase`:

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\DashToCamelCase();

print $filter->filter('this-is-my-content');
```

The above example returns 'ThisIsMyContent'.

## DashToSeparator

This filter modifies a given string such that 'words-with-dashes' are converted to 'words with
dashes'.

### Supported Options

The following options are supported for `Zend\Filter\Word\DashToSeparator`:

- **separator**: A separator char. If this is not set the separator will be a space character.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\DashToSeparator('+');
// or new Zend\Filter\Word\CamelCaseToSeparator(array('separator' => '+'));

print $filter->filter('this-is-my-content');
```

The above example returns 'this+is+my+content'.

### Default Behavior

```php
$filter = new Zend\Filter\Word\DashToSeparator();

print $filter->filter('this-is-my-content');
```

The above example returns 'this is my content'.

## DashToUnderscore

This filter modifies a given string such that 'words-with-dashes' are converted to
'words\_with\_dashes'.

### Supported Options

There are no additional options for `Zend\Filter\Word\DashToUnderscore`:

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\DashToUnderscore();

print $filter->filter('this-is-my-content');
```

The above example returns 'this\_is\_my\_content'.

## SeparatorToCamelCase

This filter modifies a given string such that 'words with separators' are converted to
'WordsWithSeparators'.

### Supported Options

The following options are supported for `Zend\Filter\Word\SeparatorToCamelCase`:

- **separator**: A separator char. If this is not set the separator will be a space character.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\SeparatorToCamelCase(':');
// or new Zend\Filter\Word\SeparatorToCamelCase(array('separator' => ':'));

print $filter->filter('this:is:my:content');
```

The above example returns 'ThisIsMyContent'.

### Default Behavior

```php
$filter = new Zend\Filter\Word\SeparatorToCamelCase();

print $filter->filter('this is my content');
```

The above example returns 'ThisIsMyContent'.

## SeparatorToDash

This filter modifies a given string such that 'words with separators' are converted to
'words-with-separators'.

### Supported Options

The following options are supported for `Zend\Filter\Word\SeparatorToDash`:

- **separator**: A separator char. If this is not set the separator will be a space character.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\SeparatorToDash(':');
// or new Zend\Filter\Word\SeparatorToDash(array('separator' => ':'));

print $filter->filter('this:is:my:content');
```

The above example returns 'this-is-my-content'.

### Default Behavior

```php
$filter = new Zend\Filter\Word\SeparatorToDash();

print $filter->filter('this is my content');
```

The above example returns 'this-is-my-content'.

## SeparatorToSeparator

This filter modifies a given string such that 'words with separators' are converted to
'words-with-separators'.

### Supported Options

The following options are supported for `Zend\Filter\Word\SeparatorToSeparator`:

- **searchSeparator**: The search separator char. If this is not set the separator will be a space
character.
- **replaceSeparator**: The replace separator char. If this is not set the separator will be a dash.

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\SeparatorToSeparator(':', '+');

print $filter->filter('this:is:my:content');
```

The above example returns 'this+is+my+content'.

### Default Behaviour

```php
$filter = new Zend\Filter\Word\SeparatorToSeparator();

print $filter->filter('this is my content');
```

The above example returns 'this-is-my-content'.

## UnderscoreToCamelCase

This filter modifies a given string such that 'words\_with\_underscores' are converted to
'WordsWithUnderscores'.

### Supported Options

There are no additional options for `Zend\Filter\Word\UnderscoreToCamelCase`:

### Basic Usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\UnderscoreToCamelCase();

print $filter->filter('this_is_my_content');
```

The above example returns 'ThisIsMyContent'.

## UnderscoreToSeparator

This filter modifies a given string such that 'words\_with\_underscores' are converted to 'words
with underscores'.

### Supported Options

The following options are supported for `Zend\Filter\Word\UnderscoreToSeparator`:

- **separator**: A separator char. If this is not set the separator will be a space character.

### Basic usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\UnderscoreToSeparator('+');
// or new Zend\Filter\Word\CamelCaseToSeparator(array('separator' => '+'));

print $filter->filter('this_is_my_content');
```

The above example returns 'this+is+my+content'.

### Default Behavior

```php
$filter = new Zend\Filter\Word\UnderscoreToSeparator();

print $filter->filter('this_is_my_content');
```

The above example returns 'this is my content'.

## UnderscoreToDash

This filter modifies a given string such that 'words\_with\_underscores' are converted to
'words-with-underscores'.

### Supported Options

There are no additional options for `Zend\Filter\Word\UnderscoreToDash`:

### Basic usage

A basic example of usage is below:

```php
$filter = new Zend\Filter\Word\UnderscoreToDash();

print $filter->filter('this_is_my_content');
```

The above example returns 'this-is-my-content'.
