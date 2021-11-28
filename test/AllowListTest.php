<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\AllowList as AllowListFilter;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\Exception;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;
use function var_export;

class AllowListTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new AllowListFilter([
            'list'   => ['test', 1],
            'strict' => true,
        ]);

        $this->assertEquals(true, $filter->getStrict());
        $this->assertEquals(['test', 1], $filter->getList());
    }

    public function testConstructorDefaults(): void
    {
        $filter = new AllowListFilter();

        $this->assertEquals(false, $filter->getStrict());
        $this->assertEquals([], $filter->getList());
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter        = $pluginManager->get('AllowList');

        $this->assertInstanceOf(AllowListFilter::class, $filter);
    }

    public function testNullListShouldThrowException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $filter = new AllowListFilter([
            'list' => null,
        ]);
    }

    public function testTraversableConvertsToArray(): void
    {
        $array  = ['test', 1];
        $obj    = new ArrayObject(['test', 1]);
        $filter = new AllowListFilter([
            'list' => $obj,
        ]);
        $this->assertEquals($array, $filter->getList());
    }

    public function testSetStrictShouldCastToBoolean(): void
    {
        $filter = new AllowListFilter([
            'strict' => 1,
        ]);
        $this->assertSame(true, $filter->getStrict());
    }

    /**
     * @param mixed $value
     * @param bool  $expected
     * @dataProvider defaultTestProvider
     */
    public function testDefault($value, $expected): void
    {
        $filter = new AllowListFilter();
        $this->assertSame($expected, $filter->filter($value));
    }

    /**
     * @param bool $strict
     * @param array $testData
     * @dataProvider listTestProvider
     */
    public function testList($strict, $list, $testData): void
    {
        $filter = new AllowListFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                $strict
            );
            $this->assertSame($expected, $filter->filter($value), $message);
        }
    }

    public static function defaultTestProvider(): array
    {
        return [
            ['test',   null],
            [0,        null],
            [0.1,      null],
            [[], null],
            [null,     null],
        ];
    }

    public static function listTestProvider(): array
    {
        return [
            [
                true, //strict
                ['test', 0],
                [
                    ['test',   'test'],
                    [0,        0],
                    [null,     null],
                    [false,    null],
                    [0.0,      null],
                    [[], null],
                ],
            ],
            [
                false, //not strict
                ['test', 0],
                [
                    ['test',   'test'],
                    [0,        0],
                    [null,     null],
                    [false,    false],
                    [0.0,      0.0],
                    [0.1,      null],
                    [[], null],
                ],
            ],
        ];
    }
}
