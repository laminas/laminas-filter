<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Filter;

use Laminas\Filter\Blacklist as BlacklistFilter;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\Exception;
use PHPUnit\Framework\TestCase;

class BlacklistTest extends TestCase
{
    public function testConstructorOptions()
    {
        $filter = new BlacklistFilter([
            'list'    => ['test', 1],
            'strict'  => true,
        ]);

        $this->assertEquals(true, $filter->getStrict());
        $this->assertEquals(['test', 1], $filter->getList());
    }

    public function testConstructorDefaults()
    {
        $filter = new BlacklistFilter();

        $this->assertEquals(false, $filter->getStrict());
        $this->assertEquals([], $filter->getList());
    }

    public function testWithPluginManager()
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter = $pluginManager->get('blacklist');

        $this->assertInstanceOf('Laminas\Filter\Blacklist', $filter);
    }

    public function testNullListShouldThrowException()
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $filter = new BlacklistFilter([
            'list' => null,
        ]);
    }

    public function testTraversableConvertsToArray()
    {
        $array = ['test', 1];
        $obj = new ArrayObject(['test', 1]);
        $filter = new BlacklistFilter([
            'list' => $obj,
        ]);
        $this->assertEquals($array, $filter->getList());
    }

    public function testSetStrictShouldCastToBoolean()
    {
        $filter = new BlacklistFilter([
            'strict' => 1
        ]);
        $this->assertSame(true, $filter->getStrict());
    }

    /**
     * @param mixed $value
     * @param bool  $expected
     * @dataProvider defaultTestProvider
     */
    public function testDefault($value, $expected)
    {
        $filter = new BlacklistFilter();
        $this->assertSame($expected, $filter->filter($value));
    }

    /**
     * @param bool $strict
     * @param array $testData
     * @dataProvider listTestProvider
     */
    public function testList($strict, $list, $testData)
    {
        $filter = new BlacklistFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            list($value, $expected) = $data;
            $message = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $strict
            );
            $this->assertSame($expected, $filter->filter($value), $message);
        }
    }

    public static function defaultTestProvider()
    {
        return [
            ['test',   'test'],
            [0,        0],
            [0.1,      0.1],
            [[],  []],
            [null,     null],
        ];
    }

    public static function listTestProvider()
    {
        return [
            [
                true, //strict
                ['test', 0],
                [
                    ['test',   null],
                    [0,        null],
                    [null,     null],
                    [false,    false],
                    [0.0,      0.0],
                    [[],  []],
                ],
            ],
            [
                false, //not strict
                ['test', 0],
                [
                    ['test',   null],
                    [0,        null],
                    [null,     null],
                    [false,    null],
                    [0.0,      null],
                    [0.1,      0.1],
                    [[],  []],
                ],
            ],
        ];
    }
}
