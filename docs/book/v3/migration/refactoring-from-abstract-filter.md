# Refactoring Custom Filters away from `AbstractFilter`

For this example, we will define a filter that when given a boolean, one of 2 specific, configurable values will be returned.

To begin with, here is the implementation that extended from `AbstractFilter`:

```php
use Laminas\Filter\AbstractFilter;

class MyFilter extends AbstractFilter
{
    public function filter($value)
    {
        if (! is_bool($value)) {
            return $value;
        }
        
        return $value
            ? $this->getTrueValue()
            : $this->getFalseValue();
    }
    
    public function getTrueValue() {
        return $this->options['true_value']
    }
    
    public function getFalseValue() {
        return $this->options['false_value']
    }
    
    public function setTrueValue(string $value) {
        $this->options['true_value'] = $value;
    }
    
    public function setFalseValue(string $value) {
        $this->options['false_value'] = $value;
    }
}
```

In the above example, it is assumed that at some point, `AbstractFilter::setOptions()` would be called to provide options at runtime.
This behaviour should no longer be relied upon and options should be passed directly to your filter constructor so that it cannot be constructed in an invalid state.

Furthermore, setters and getters for options are generally unnecessary and are typically added out of convention or a desire to unit test the option values in some way.
We encourage you to drop these option setters and getters and instead test your filters by asserting expected outputs from a wide range of inputs whilst varying options to assure desired behaviour.

Here is an example of the above class refactored for `laminas-filter` v3:

```php
use Laminas\Filter\FilterInterface;

final class MyFilter implements FilterInterface
{
    private readonly string $trueValue;
    private readonly string $falseValue;
    
    public function __construct(array $options)
    {
        // Validation of $options omitted for brevity
        $this->trueValue = $options['true_value'];
        $this->falseValue = $options['false_value'];
    }
    
    public function filter(mixed $value): mixed
    {
        if (! is_bool($value)) {
            return $value;
        }
        
        return $value
            ? $this->trueValue
            : $this->falseValue;
    }
    
    public function __invoke(mixed $value): mixed {
        return $this->filter($value);
    }
}
```
