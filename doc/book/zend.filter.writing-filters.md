# Writing Filters

`Zend\Filter` supplies a set of commonly needed filters, but developers will often need to write
custom filters for their particular use cases. The task of writing a custom filter is facilitated by
implementing `Zend\Filter\FilterInterface`.

`Zend\Filter\FilterInterface` defines a single method, `filter()`, that may be implemented by user
classes.

## Example

The following example demonstrates how to write a custom filter:

```php
namespace Application\Filter;

use Zend\Filter\FilterInterface;

class MyFilter implements FilterInterface
{
    public function filter($value)
    {
        // perform some transformation upon $value to arrive on $valueFiltered

        return $valueFiltered;
    }
}
```

To attach an instance of the filter defined above to a filter chain:

```php
$filterChain = new Zend\Filter\FilterChain();
$filterChain->attach(new Application\Filter\MyFilter());
```
