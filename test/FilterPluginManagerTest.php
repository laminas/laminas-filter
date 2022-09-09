<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\Exception\RuntimeException;
use Laminas\Filter\FilterPluginManager;
use Laminas\Filter\ToInt;
use Laminas\Filter\Word\SeparatorToSeparator;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Throwable;

use function method_exists;

class FilterPluginManagerTest extends TestCase
{
    private FilterPluginManager $filters;

    public function setUp(): void
    {
        $this->filters = new FilterPluginManager(new ServiceManager());
    }

    public function testFilterSuccessfullyRetrieved(): void
    {
        $filter = $this->filters->get('int');
        $this->assertInstanceOf(ToInt::class, $filter);
    }

    public function testRegisteringInvalidFilterRaisesException(): void
    {
        $this->expectException($this->getInvalidServiceException());
        /** @psalm-suppress InvalidArgument */
        $this->filters->setService('test', $this);
    }

    public function testLoadingInvalidFilterRaisesException(): void
    {
        $this->filters->setInvokableClass('test', static::class);
        $this->expectException($this->getInvalidServiceException());
        $this->filters->get('test');
    }

    /**
     * @group 7169
     */
    public function testFilterSuccessfullyConstructed(): void
    {
        $searchSeparator      = ';';
        $replacementSeparator = '|';

        $options = [
            'search_separator'      => $searchSeparator,
            'replacement_separator' => $replacementSeparator,
        ];

        $filter = $this->filters->get('wordseparatortoseparator', $options);

        $this->assertInstanceOf(SeparatorToSeparator::class, $filter);
        $this->assertSame($searchSeparator, $filter->getSearchSeparator());
        $this->assertSame($replacementSeparator, $filter->getReplacementSeparator());
    }

    /**
     * @group 7169
     */
    public function testFiltersConstructedAreDifferent(): void
    {
        $filterOne = $this->filters->get(
            'wordseparatortoseparator',
            [
                'search_separator'      => ';',
                'replacement_separator' => '|',
            ]
        );

        $filterTwo = $this->filters->get(
            'wordseparatortoseparator',
            [
                'search_separator'      => '.',
                'replacement_separator' => ',',
            ]
        );

        $this->assertNotEquals($filterOne, $filterTwo);
    }

    /** @return class-string<Throwable> */
    protected function getInvalidServiceException(): string
    {
        if (method_exists($this->filters, 'configure')) {
            return InvalidServiceException::class;
        }
        return RuntimeException::class;
    }
}
