# String Inflection

`Laminas\Filter\Inflector` is a general purpose tool for rule-based inflection of strings to a given target.

As an example, you may find you need to transform MixedCase or camelCasedWords into a path;
for readability, OS policies, or other reasons, you also need to lower case this;
and finally, you want to separate the words using a dash (`-`).
An inflector can do this for you.

`Laminas\Filter\Inflector` implements `Laminas\Filter\FilterInterface`; you perform
inflection by calling `filter()` on the object instance.

## Options Reference

- `target` **(required)** The target string containing the placeholders to replace
- `rules` **(required)** An array of inflection rules
- `targetReplacementIdentifier` Can be used to override the default placeholder delimiter of `':'`
- `throwTargetExceptionsOn` A boolean to indicate whether an exception should be thrown for un-processed placeholders *(`true` by default)*

## Example: Transform Mixed Case and Camel Cased Text to Another Format

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'target' => 'pages/:page.:suffix',
    'rules'  => [
        ':page'  => [
            Laminas\Filter\Word\CamelCaseToDash::class,
            Laminas\Filter\StringToLower::class,
        ],
        'suffix' => 'html',
    ],
]);

$filtered = $inflector->filter(['page' => 'camelCasedWords']);
// pages/camel-cased-words.html

$filtered = $inflector->filter(['page' => 'this_is_not_camel_cased']);
// pages/this_is_not_camel_cased.html
```

## Operation

An inflector requires a **target** and one or more **rules**.

A `target` is basically a string that defines placeholders for variables you wish to substitute.
These are specified by prefixing the placeholder name with a `:`, for example `:script`.

When calling `filter()`, you then pass in an array of key and value pairs corresponding to the placeholder names in the target.

Each variable in the target can have zero or more rules associated with them.
Rules may be either **static** or refer to a filter type, or callable.
**Static Rules** define straight-forward string replacement.
**Filter Rules** define one or more filters that operate on the value.

Filters can be specified using:

- The FQCN of a filter such as `Laminas\Filter\StringToLower::class`
- An alias of a known filter such as `stringtolower`
- A concrete filter instance, i.e. `new Laminas\Filter\StringToLower()`
- Any callable, for example a closure such as `static fn (string $input): string => strtolower($input)`

### Using Custom Filters

`Laminas\Filter\Inflector` uses `Laminas\Filter\FilterPluginManager` to manage loading filters to use with inflection.
By default, all [standard filters](standard-filters.md) are available by referencing any FQCN, or known alias of the filter type.
If you have configured your application with [custom filters](writing-filters.md), these will also be available in any rules you define.

TIP: Try to prefer fully qualified class names *(FQCNs)* rather than aliases or 'short names'.
It will be easier for your IDE or text editor to identify specific filter usage when you use FQCNs.

### Setting the Inflector Target

As previously mentioned, the *required* option `target` is a string with some placeholders for variables.
Placeholders take the form of an identifier, a colon (`:`) by default, followed by a variable name: `:script`, `:path`, etc.
The `filter()` method looks for the identifier followed by the variable name being replaced.

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'target' => 'pages/:page.:suffix',
    // ... Other Options
]);
```

### Changing the Target Placeholder Delimiter

By setting the `targetReplacementIdentifier` option to the delimiter of your choice, you can prevent issues where the delimiter might need to be part of the `target` option:

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'target' => '?host:?port/?page.?suffix',
    'targetReplacementIdentifier' => '?',
    'rules' => [
        'host' => 'example.com',
        'port' => '80',
        'page' => 'anything',
        'suffix' => 'html',
    ],
]);
$inflector->filter(['page' => 'index']); // example.com:80/index.html
```

## Inflection Rules

As mentioned in the introduction, there are two types of rules: **static** and **filter-based**.

### Specifying Static Rules

Static rules are key-value items, where `['placeholder' => 'replacement']`.
Static rule names lack a leading `':'`.

Filter input can be used to override the replacement value for a placeholder:

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'target' => ':a,:b,:c',
    'rules' => [
        'a' => 'one',
        'b' => 'two',
        'c' => 'three',    
    ],
]);

$inflector->filter(['c' => 'foo']); // 'one,two,foo'
```

NOTE: **Order is important**
It is important to note that regardless of the rule type, either static of filter based; the order is very important.
More specific names, or names that might contain other rule names, must be added before the least specific names.
For example, assuming two rule names `moduleDir` and `module`, the `moduleDir` rule should appear before module since the word `module` is contained within `moduleDir`.
If `module` were added before `moduleDir`, `module` will match part of `moduleDir` and process it leaving `Dir` inside of the target un-inflected.

### Filter-Based Inflector Rules

Filters may be used as inflector rules as well. Just like static rules, these are bound to a target variable; unlike static rules, you may define multiple filters to use when inflecting.
These filters are processed in order, so be careful to register them in an order that makes sense for the data you receive.

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'target' => ':script.:suffix',
    'rules' => [
        ':script' => [
            Laminas\Filter\Word\CamelCaseToDash::class,
            Laminas\Filter\StringToLower::class,
        ],
        'suffix' => 'php',
    ],
]);

$inflector->filter(['script' => 'MyScript']); // "my-script.php"
```

### Avoiding Exceptions

Finally, the option `throwTargetExceptionsOn` defines whether an exception is thrown for un-processed placeholders.

By default, the following example will cause an exception:

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'target' => ':script.:suffix',
    'rules' => [
        ':script' => [
            Laminas\Filter\Word\CamelCaseToDash::class,
            Laminas\Filter\StringToLower::class,
        ],
        'suffix' => 'php',
    ],
]);

$inflector->filter(['wrong-key' => 'SomeValue']); // Laminas\Filter\Exception\RuntimeException
```

By setting `throwTargetExceptionsOn` to `false`, no exception will be thrown, but the filter output will contain un-processed placeholder values:

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = new Laminas\Filter\Inflector($pluginManager, [
    'throwTargetExceptionsOn' => false,
    'target' => ':script.:suffix',
    'rules' => [
        ':script' => [
            Laminas\Filter\Word\CamelCaseToDash::class,
            Laminas\Filter\StringToLower::class,
        ],
        'suffix' => 'php',
    ],
]);

$inflector->filter(['wrong-key' => 'SomeValue']); // ':script.php'
```

## Filterable Values

The inflector can only process arrays or objects. Other types will be returned as-is, for example:

```php
$inflector->filter('string'); // 'string
```

When an object is passed, it's public properties are extracted into an array using [`get_object_vars`](https://www.php.net/get_object_vars).

This can be useful when you wish to use a readonly value object to define a specific, predictable set of data to be used for inflection with consistent type guarantees.

## Filter Plugin Manager Requirement

In all the examples so far, you will notice that the first constructor argument is an instance of `Laminas\Filter\FilterPluginManager`. The plugin manager is used to create filter instances for use with inflection and cannot be omitted.

During general usage of filters, you will typically be configuring filters as part of an [input filter specification with laminas-inputfilter](https://docs.laminas.dev/laminas-inputfilter/) or `laminas-form`. In these cases, the constructor dependencies are resolved automatically and you only need to concern yourself with setting the correct options.

If you find the need to use the inflector stand-alone, you can use `Laminas\Filter\FilterPluginManager::build()` to create an instance by only specifying options:

```php
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$inflector = $pluginManager->build(
    Laminas\Filter\Inflector::class,
    [
        'target' => ':script.:suffix',
        'rules' => [
            ':script' => [
                Laminas\Filter\Word\CamelCaseToDash::class,
                Laminas\Filter\StringToLower::class,
            ],
            'suffix' => 'php',
        ],
    ],
);
```
