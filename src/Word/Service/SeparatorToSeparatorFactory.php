<?php

declare(strict_types=1);

namespace Laminas\Filter\Word\Service;

use Laminas\Filter\Word\SeparatorToSeparator;
use Psr\Container\ContainerInterface;

use function assert;
use function is_string;

final class SeparatorToSeparatorFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null,
    ): SeparatorToSeparator {
        $options = $options ?? [];

        $searchSeparator      = $options['search_separator'] ?? ' ';
        $replacementSeparator = $options['replacement_separator'] ?? '-';

        assert(is_string($searchSeparator));
        assert(is_string($replacementSeparator));

        return new SeparatorToSeparator(
            $searchSeparator,
            $replacementSeparator
        );
    }
}
