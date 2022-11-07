<?php

declare(strict_types=1);

namespace Laminas\Filter\Word\Service;

use Laminas\Filter\Word\SeparatorToSeparator;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;
use Traversable;

use function get_debug_type;
use function is_array;
use function iterator_to_array;
use function sprintf;

class SeparatorToSeparatorFactory implements FactoryInterface
{
    /**
     * Options to pass to the constructor (when used in v2), if any.
     *
     * @param null|array
     */
    private array $creationOptions = [];

    public function __construct($creationOptions = null)
    {
        if (null === $creationOptions) {
            return;
        }

        if ($creationOptions instanceof Traversable) {
            $creationOptions = iterator_to_array($creationOptions);
        }

        if (! is_array($creationOptions)) {
            throw new InvalidServiceException(sprintf(
                '%s cannot use non-array, non-traversable creation options; received %s',
                self::class,
                get_debug_type($creationOptions)
            ));
        }

        $this->creationOptions = $creationOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new SeparatorToSeparator(
            $options['search_separator'] ?? ' ',
            $options['replacement_separator'] ?? '-'
        );
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, self::class, $this->creationOptions);
    }

    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
