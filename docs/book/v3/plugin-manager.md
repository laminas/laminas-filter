# Plugin Manager

The plugin manager of laminas-filter is called "filter plugin manager" – `Laminas\Filter\FilterPluginManager`.

The filter plugin manager is a specialized service manager that provides access to filter classes.
It is used to create and manage instances of filters, which are used to transform data.
The filter plugin manager can be created using a service container which implements the [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/).

## Creating a Filter Plugin Manager

The following example shows how to create a filter plugin manager using [laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/), the dependency injection container provided by Laminas:

```php
$filterPluginManager = new Laminas\Filter\FilterPluginManager(
    new Laminas\ServiceManager\ServiceManager()
);
```

## Retrieving Filters

Filters can be retrieved from the filter plugin manager using the `get()` method.
The filter plugin manager will automatically create an instance of the requested filter if it does not already exist.

Fetching a filter by its class name:

```php
$filter = $filterPluginManager->get(Laminas\Filter\StringTrim::class);
```

Fetching a filter by its alias:

```php
$filter = $filterPluginManager->get('stringtrim');
$filter = $filterPluginManager->get('stringTrim');
$filter = $filterPluginManager->get('StringTrim');
```

TIP: The filter plugin manager is not really necessary for all filters which are provided by laminas-filter, _[beside the filter chains](filter-chains.md)_.
Because all filters can be created without external dependencies, directly by using the constructor.
(The filter chains require the filter plugin manager as a dependency to create the filters they contain.)

## Registering Custom Filters

Custom filters can be registered with the filter plugin manager using the `configure` method or bypassing configuration to the constructor.

NOTE: The manager is based on the [plugin manager of laminas-servicemanager](https://docs.laminas.dev/laminas-servicemanager/plugin-managers/) and the [configuration follows the exact same pattern](https://docs.laminas.dev/laminas-servicemanager/configuring-the-service-manager/) as for a normal service manager of laminas-servicemanager.

### Using the `configure` Method

The `configure` method accepts an array of configuration options:

```php
$filterPluginManager = new Laminas\Filter\FilterPluginManager(
    new Laminas\ServiceManager\ServiceManager()
);
$filterPluginManager->configure([
    'factories' => [
        Album\Filter\ExampleFilter::class => Album\Filter\ExampleFilterFactory::class,
    ],
    'aliases' => [
        'examplefilter' => Album\Filter\ExampleFilter::class,
    ],
    'abstract_factories' => [],
    'delegators'         => [],
    // …
]);
```

### Using the Constructor

The constructor of the filter plugin manager accepts the same array of configuration options:

```php
$filterPluginManager = new Laminas\Filter\FilterPluginManager(
    new Laminas\ServiceManager\ServiceManager(),
    [
        'factories' => [
            Album\Filter\ExampleFilter::class => Album\Filter\ExampleFilterFactory::class,
        ],
        'aliases' => [
            'examplefilter' => Album\Filter\ExampleFilter::class,
        ],
        'abstract_factories' => [],
        'delegators'         => [],
        // …
    ]
);
```

### Fetching the Registered Custom Filter

The filter plugin manager can create the custom filter by the related class name:

```php
$filter = $filterPluginManager->get(ExampleFilter::class);
```

Or by its alias, if it has been registered:

```php
$filter = $filterPluginManager->get('examplefilter');
```

## Fetch a Custom Filter Without Registration

The filter plugin manager allows fetching custom filters **without prior registration** with the manager.

The following example creates a custom filter that does not require any dependencies:

```php
final class ExampleFilter implements Laminas\Filter\FilterInterface
{
    public function filter(mixed $value): mixed
    {
        // …
    }
}
```

The filter plugin manager can create the custom filter by the related class name:

```php
$filter = $filterPluginManager->get(ExampleFilter::class);
```

The manager uses [the factory `Laminas\ServiceManager\Factory\InvokableFactory`](https://docs.laminas.dev/laminas-servicemanager/v4/configuring-the-service-manager/#factories) to instantiate the filter, and will also pass the options for the filter to the constructor:

```php
$filter = $filterPluginManager->get(
    ExampleFilter::class,
    [
        // Options for the filter
    ]
);
```

WARNING: An alias for the custom filter is not automatically created.
If an alias is to be used, [it must be registered manually](#registering-custom-filters) in the filter plugin manager configuration.

## Why Is the Filter Plugin Manager Relevant?

The filter plugin manager is relevant because it is used in [input filters of laminas-inputfilter](https://docs.laminas.dev/laminas-inputfilter/), stand-alone or in [forms of laminas-form](https://docs.laminas.dev/laminas-form/).
Internally, the manager is used to create filters for the input filter.

The following form illustrates how the filters are defined for the input filter.
The filters are automatically fetched from the filter plugin manager by their class name or alias.

<!-- markdownlint-disable MD033 -->
<pre class="language-php" data-line="33-34"><code>
namespace Album\Form;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\StringLength;

final class AlbumForm extends Form implements InputFilterProviderInterface
{
    public function init(): void
    {
        // Add form elements
        $this->add([
            'name'    => 'title',
            'type'    => Text::class,
            'options' => [
                'label' => 'Title',
            ],
        ]);

        // …
    }

    public function getInputFilterSpecification(): array
    {
        return [
            // Add inputs
            [
                'name'    => 'title',
                'filters' => [
                    ['name' => StripTags::class],
                    ['name' => StringTrim::class],
                ],
                'validators' => [
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'min' => 1,
                            'max' => 100,
                        ],
                    ],
                ],
            ],
            // …
        ];
    }
}
</code></pre>
<!-- markdownlint-enable MD033 -->

## Learn More

- [Configuring the service manager](https://docs.laminas.dev/laminas-servicemanager/configuring-the-service-manager/)
- [Using Input Filters in Forms of laminas-form](https://docs.laminas.dev/laminas-inputfilter/cookbook/input-filter-in-forms/)