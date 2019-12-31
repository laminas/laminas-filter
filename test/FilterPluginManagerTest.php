<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;

/**
 * @group      Laminas_Filter
 */
class FilterPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filters = new FilterPluginManager();
    }

    public function testFilterSuccessfullyRetrieved()
    {
        $filter = $this->filters->get('int');
        $this->assertInstanceOf('Laminas\Filter\ToInt', $filter);
    }

    public function testRegisteringInvalidFilterRaisesException()
    {
        $this->setExpectedException('Laminas\Filter\Exception\RuntimeException');
        $this->filters->setService('test', $this);
    }

    public function testLoadingInvalidFilterRaisesException()
    {
        $this->filters->setInvokableClass('test', get_class($this));
        $this->setExpectedException('Laminas\Filter\Exception\RuntimeException');
        $this->filters->get('test');
    }

    /**
     * @group 7169
     */
    public function testFilterSuccessfullyConstructed()
    {
        $search_separator = ';';
        $replacement_separator = '|';
        $options = array(
            'search_separator'      => $search_separator,
            'replacement_separator' => $replacement_separator,
        );
        $filter = $this->filters->get('wordseparatortoseparator', $options);
        $this->assertInstanceOf('Laminas\Filter\Word\SeparatorToSeparator', $filter);
        $this->assertEquals(';', $filter->getSearchSeparator());
        $this->assertEquals('|', $filter->getReplacementSeparator());
    }

    /**
     * @group 7169
     */
    public function testFiltersConstructedAreDifferent()
    {
        $filterOne = $this->filters->get(
            'wordseparatortoseparator',
            array(
                'search_separator'      => ';',
                'replacement_separator' => '|',
            )
        );
        $filterTwo = $this->filters->get(
            'wordseparatortoseparator',
            array(
                'search_separator'      => '.',
                'replacement_separator' => ',',
            )
        );

        $this->assertNotEquals($filterOne, $filterTwo);
    }
}
