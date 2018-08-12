<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Filter;

use PHPUnit\Framework\TestCase;
use Zend\Filter\Exception\InvalidArgumentException;
use Zend\Filter\StringSuffix as StringSuffixFilter;

class StringSuffixTest extends TestCase
{
    /**
     * @var StringSuffixFilter
     */
    protected $filter;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->filter = new StringSuffixFilter();
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = $this->filter;

        $suffix = 'ABC123';
        $filter->setSuffix($suffix);

        $this->assertStringEndsWith($suffix, $filter('sample'));
    }

    public function testWithoutSuffix()
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a "suffix" option; none given');
        $filter('sample');
    }

    public function testNonStringSuffix()
    {
        $filter = $this->filter;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects "suffix" to be string');

        $suffix = [];
        $filter->setSuffix($suffix);
        $filter('sample');
    }

    public function testNonScalarInput()
    {
        $filter = $this->filter;

        $suffix = 'ABC123';
        $filter->setSuffix($suffix);

        $this->assertInstanceOf(\stdClass::class, $filter(new \stdClass()));
    }
}
