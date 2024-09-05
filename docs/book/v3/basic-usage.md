# Basic Usage

Following is a basic example of using a filter upon two input data, the
ampersand (`&`) and double quote (`"`) characters:

```php
$htmlEntities = new Laminas\Filter\HtmlEntities();

echo $htmlEntities->filter('&'); // &amp;
echo $htmlEntities->filter('"'); // &quot;
```

## Filtering via Invokables

The interface `Laminas\Filter\FilterInterface` contains also the magic method `__invoke()`.
This allows to use a filter as an invokable:

```php
$strtolower = new Laminas\Filter\StringToLower;

echo $strtolower('I LOVE Laminas!'); // i love laminas!
$laminaslove = $strtolower('I LOVE Laminas!');
```

## Double Filtering

When using two filters in succession, you have to keep in mind that it is
often not possible to get the original output by using the opposite filter. Take
the following example:

```php
$original = 'my_original_content';

// Attach a filter
$filter   = new Laminas\Filter\Word\UnderscoreToCamelCase();
$filtered = $filter->filter($original);

// Use it's opposite
$filter2  = new Laminas\Filter\Word\CamelCaseToUnderscore();
$filtered = $filter2->filter($filtered)
```

The above code example could lead to the impression that you will get the
original output after the second filter has been applied. But thinking logically
this is not the case. After applying the first filter, `my_original_content` will
be changed to `MyOriginalContent`. But after applying the second filter, the result
is `My_Original_Content`.

As you can see it is not always possible to get the original output by using a
filter which seems to be the opposite. It depends on the filter and also on the
given input.
