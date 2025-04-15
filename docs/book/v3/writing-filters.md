# Writing Filters

`Laminas\Filter` supplies a set of commonly needed filters, but developers will
often need to write custom filters for their particular use cases.
You can do so by writing classes that implement `Laminas\Filter\FilterInterface`, which defines two methods, `filter()` and `__invoke()`.

## Example

```php
namespace Application\Filter;

use Laminas\Filter\FilterInterface;

final class MyFilter implements FilterInterface
{
    public function filter(mixed $value): mixed
    {
        // perform some transformation upon $value to arrive at $valueFiltered

        return $valueFiltered;
    }
    
    public function __invoke(mixed $value): mixed {
        return $this->filter($value);    
    }
}
```

To attach an instance of the filter defined above to a filter chain:

```php
$filterChain = new Laminas\Filter\FilterChain($pluginManager);
$filterChain->attach(new Application\Filter\MyFilter());
```

### Narrowing Return Type Candidates for Static Analysis Tools

`InputFilterInterface` declares a template that you can add to custom filter implementations to improve type inference of filtered values.
Whilst this is only useful when using filters directly, it can also help reduce unit testing burden if you are a user of Psalm or PHPStan.

Here's a trivial example to illustrate the generic template applied to a custom filter:

```php
use Laminas\Filter\FilterInterface;

/** @implements FilterInterface<int<0,1>> */
final class MyFilter implements FilterInterface
{
    public function filter(mixed $value): mixed
    {
        if (! is_bool($value)) {
            return $value;
        }
        
        return $value ? 1 : 0;
    }
    
    public function __invoke(mixed $value): mixed {
        return $this->filter($value);    
    }
}
```

In this example, the return type will be narrowed to `mixed|1|0`

## Registering Custom Filters with the Plugin Manager

In both Laminas MVC and Mezzio applications, the top-level `filters` configuration key can be used to register filters with the plugin manager in standard Service Manager format:

```php
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'filters' => [
        'factories' => [
            My\Filter\FilterOne::class => InvokableFactory::class,
            My\Filter\FilterTwo::class => My\Filter\SomeCustomFactory::class,
        ],
        'aliases' => [
            'filterOne' => My\Filter\FilterOne::class,
            'filterTwo' => My\Filter\FilterTwo::class,
        ],
    ],
];
```

Assuming the configuration above is merged into your application configuration, either by way of a dedicated configuration file, or via an MVC Module class or Mezzio Config Provider, you would be able to retrieve filter instances from the plugin manager by FQCN or alias.
