<?php

declare(strict_types=1);

namespace LaminasTest\Filter\StaticAnalysis;

use Laminas\Filter;

/** @psalm-suppress UnusedClass */
final class BooleanFilterChecks
{
    public function constructorAcceptsSingleTypeConstant(): Filter\Boolean
    {
        return new Filter\Boolean(['type' => Filter\Boolean::TYPE_FLOAT]);
    }

    public function constructorAcceptsListOfConstants(): Filter\Boolean
    {
        return new Filter\Boolean([
            'type' => [
                Filter\Boolean::TYPE_EMPTY_ARRAY,
                Filter\Boolean::TYPE_FALSE_STRING,
            ],
        ]);
    }

    public function constructorAcceptsIntMaskOfConstants(): Filter\Boolean
    {
        return new Filter\Boolean(['type' => Filter\Boolean::TYPE_ALL ^ Filter\Boolean::TYPE_FLOAT]);
    }

    public function constructorAcceptsNamedType(): Filter\Boolean
    {
        return new Filter\Boolean(['type' => 'localized']);
    }

    public function constructorAcceptsOptionsArray(): Filter\Boolean
    {
        return new Filter\Boolean([
            'type'    => Filter\Boolean::TYPE_FALSE_STRING | Filter\Boolean::TYPE_FLOAT,
            'casting' => false,
        ]);
    }
}
