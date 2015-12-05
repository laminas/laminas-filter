<?php

namespace Zend\Filter\Word\Service;

use Interop\Container\ContainerInterface;
use Zend\Filter\Word\SeparatorToSeparator;
use Zend\ServiceManager\Factory\FactoryInterface;

class SeparatorToSeparatorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SeparatorToSeparator(
            isset($options['search_separator']) ? $options['search_separator'] : ' ',
            isset($options['replacement_separator']) ? $options['replacement_separator'] : '-'
        );
    }
}
