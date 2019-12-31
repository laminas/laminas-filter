<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\FilterPluginManager;

/**
 * @category   Laminas
 * @package    Laminas_Filter
 * @subpackage UnitTests
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
        $this->assertInstanceOf('Laminas\Filter\Int', $filter);
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
}
