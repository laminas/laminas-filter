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

use function method_exists;

class FilterPluginManagerTest extends TestCase
{
    /** @var FilterPluginManager */
    private $filters;

    public function setUp(): void
    {
        $this->filters = new FilterPluginManager(new ServiceManager());
    }

    public function testFilterSuccessfullyRetrieved()
    {
        $filter = $this->filters->get('int');
        $this->assertInstanceOf(ToInt::class, $filter);
    }

    public function testRegisteringInvalidFilterRaisesException()
    {
        $this->expectException($this->getInvalidServiceException());
        $this->filters->setService('test', $this);
        $this->filters->get('test');
    }

    public function testLoadingInvalidFilterRaisesException()
    {
        $this->filters->setInvokableClass('test', static::class);
        $this->expectException($this->getInvalidServiceException());
        $this->filters->get('test');
    }

    /**
     * @group 7169
     */
    public function testFilterSuccessfullyConstructed()
    {
        $searchSeparator      = ';';
        $replacementSeparator = '|';

        $options = [
            'search_separator'      => $searchSeparator,
            'replacement_separator' => $replacementSeparator,
        ];

        $filter = $this->filters->get('wordseparatortoseparator', $options);

        $this->assertInstanceOf(SeparatorToSeparator::class, $filter);
        $this->assertEquals($searchSeparator, $filter->getSearchSeparator());
        $this->assertEquals($replacementSeparator, $filter->getReplacementSeparator());
    }

    /**
     * @group 7169
     */
    public function testFiltersConstructedAreDifferent()
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

    protected function getInvalidServiceException()
    {
        if (method_exists($this->filters, 'configure')) {
            return InvalidServiceException::class;
        }
        return RuntimeException::class;
    }
}
