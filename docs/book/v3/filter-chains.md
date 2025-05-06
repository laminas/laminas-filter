# Filter Chains

Filter chains provide a way of grouping multiple filters together.
They operate on the input by fulfilling the `FilterInterface` contract in the same way as all the other filters.

You can enqueue as many filters as you like via a straight-forward api, attaching either ready-to-use filter instances, arbitrary callables or by using a declarative array format.

## Getting a Filter Chain Instance

The filter chain has a constructor dependency on the `FilterPluginManager`, so whilst you can manually create a filter chain from scratch, it is normally easier to retrieve one from the plugin manager

```php
assert($container instanceof Psr\Container\ContainerInterface);
$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);

// Create a Filter Chain instance manually:
$chain = new Laminas\Filter\FilterChain($pluginManager);

// Fetch an empty filter chain from the plugin manager
$chain = $pluginManager->get(Laminas\Filter\FilterChain::class);

// Build a ready-to-use filter chain via the plugin manager
$chain = $pluginManager->build(Laminas\Filter\FilterChain::class, [
    // ...(Filter Chain Configuration))
]);
```

## Adding Filters to the Chain

The following examples provide 3 ways of building a chain that performs the following:

- Trim the input string using `Laminas\Filter\StringTrim`
- Make the string lowercase using `Laminas\Filter\StringToLower`
- Reverse the string using a closure

### 1. Attaching Filter Instances to the Chain

```php
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;

$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);

$chain = $pluginManager->get(FilterChain::class);

$chain->attach(new StringTrim()); // directly instantiate the filter
$chain->attach($pluginManager->get(StringToLower::class));
$chain->attach(static fn (string $value): string => strrev($value));

print $chain->filter(' OOF '); // 'foo'
```

### 2. Building the Chain with Array Configuration

```php
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;

$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$chain = $pluginManager->build(FilterChain::class, [
    'filters' => [
        ['name' => StringTrim::class],
        ['name' => StringToLower::class],
    ],
    'callbacks' => [
        ['callback' => static fn (string $value): string => strrev($value)],
    ],
]);

print $chain->filter(' OOF '); // 'foo'
```

### 3. Adding Filters to the Chain by Name

```php
use Laminas\Filter\FilterChain;
use Laminas\Filter\StringToLower;
use Laminas\Filter\StringTrim;

$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);

$chain = $pluginManager->get(FilterChain::class);

// `attachByName` retrieves the filter from the composed plugin manager:
$chain->attachByName(StringTrim::class);
$chain->attachByName(StringToLower::class);
// We must still use `attach` to add the closure:
$chain->attach(static fn (string $value): string => strrev($value));

print $chain->filter(' OOF '); // 'foo'
```

By default, filters execute in the order they are added to the filter chain.
In the above examples, the input is trimmed first, then converted to lowercase and finally reversed.

## Types of Attachable Filters

Any object that implements `Laminas\Filter\FilterInterface` may be used in a filter chain.
Additionally, you can provide any type of `callable`, however it should match the signature `fn (mixed $value): mixed`. You are free to narrow the types from mixed, but given the simple contract of `FilterInterface` it is often better practice to [write a custom filter](writing-filters.md) and register it with the plugin manager.

## Setting Filter Chain Order

For each filter added to the `FilterChain`, you can set a priority to define the chain order.
Higher values indicate higher priority (execute first), while lower and/or negative values indicate lower priority (execute last).
The default priority is `1000`.

In the following example, an uppercase prefix is applied after the input has been converted to lower case, even though the prefix filter is added to the chain first:

```php
// Create a filter chain and add filters to the chain
$filterChain = $pluginManager->get(Laminas\Filter\FilterChain::class);
$filterChain->attach(new Laminas\Filter\StringPrefix(['prefix' => 'FOO: ']));
$filterChain->attach(new Laminas\Filter\StringToLower(), 500);

print $filterChain->filter('BAR'); // 'FOO: bar'
```

## Array Configuration

As previously noted, you can define filter chains using a configuration array.
The exact specification of this array is as follows:

```php
$filterChainConfig = [
    'filters' => [
        [
            'name' => SomeFilter::class, // Required. Must be an alias or a FQCN registered in the plugin manager
            'options' => [ /* ... */ ], // Optional. Provide options specific to the required filter
            'priority' => 500, // Optional. Set the execution priority of the filter (Default 1000)
        ],
    ],
    'callbacks' => [
        [
            'callback' => static fn (string $in): string => strrev($in), // Required. Any type of PHP callable
            'priority' => 500, // Optional priority, default 1000
        ],
        [
            'callback' => new Laminas\Filter\StringToLower(), // Any object implementing FilterInterface
        ],
    ],
];
```

NOTE: **Callbacks are Registered First**
It's important to note that internally, `callbacks` are registered _first_.
This means that if you do not specify priorities when using the array configuration format, the filter execution order may not be what you want.

## Using the Plugin Manager

As with other plugin managers in the laminas ecosystem, you can retrieve filters either by fully qualified class name, or by any configured alias of that class.
For example, the `Laminas\Filter\StringToLower` filter is aliased to `stringToLower`, therefore, calling `$pluginManager->get('stringToLower')` will yield an instance of this filter.

When using filter chain, you must remember to [register custom filters](writing-filters.md#registering-custom-filters-with-the-plugin-manager) with the plugin manager correctly if you wish to reference your filters by FQCN or alias.

## Immutable Filter Chain

In addition to the standard `FilterChain`, laminas-filter also provides an immutable variant called `ImmutableFilterChain`. This class is useful when you want to ensure that a filter chain's configuration cannot be modified after creation.

Since the `ImmutableFilterChain` implements the same `FilterInterface` as the standard `FilterChain`, they can be used interchangeably in contexts that only rely on the filter method.

### Creating an ImmutableFilterChain

```php

$pluginManager = $container->get(Laminas\Filter\FilterPluginManager::class);
$chain = new ImmutableFilterChain(
    $pluginManager,
    [
        'filters' => [
            ['name' => StringTrim::class],
            ['name' => StringToLower::class],
        ],
    ]
);
// attach and attachByName will return a new instance
$chainRev = $chain->attach(static fn (string $value): string => strrev($value));

print $chain->filter(' OOF '); // 'oof'
print $chainRev->filter(' OOF '); // 'foo'
```
