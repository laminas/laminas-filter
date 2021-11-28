<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\DenyList as DenyListFilter;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\Exception;
use PHPUnit\Framework\TestCase;

use function gettype;
use function sprintf;
use function var_export;

class DenyListTest extends TestCase
{
    public function testConstructorOptions(): void
    {
        $filter = new DenyListFilter([
            'list'   => ['test', 1],
            'strict' => true,
        ]);

        $this->assertEquals(true, $filter->getStrict());
        $this->assertEquals(['test', 1], $filter->getList());
    }

    public function testConstructorDefaults(): void
    {
        $filter = new DenyListFilter();

        $this->assertEquals(false, $filter->getStrict());
        $this->assertEquals([], $filter->getList());
    }

    public function testWithPluginManager(): void
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter        = $pluginManager->get('DenyList');

        $this->assertInstanceOf(DenyListFilter::class, $filter);
    }

    public function testNullListShouldThrowException(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        $filter = new DenyListFilter([
            'list' => null,
        ]);
    }

    public function testTraversableConvertsToArray(): void
    {
        $array  = ['test', 1];
        $obj    = new ArrayObject(['test', 1]);
        $filter = new DenyListFilter([
            'list' => $obj,
        ]);
        $this->assertEquals($array, $filter->getList());
    }

    public function testSetStrictShouldCastToBoolean(): void
    {
        $filter = new DenyListFilter([
            'strict' => 1,
        ]);
        $this->assertSame(true, $filter->getStrict());
    }

    /**
     * @param mixed $value
     * @param mixed  $expected
     * @dataProvider defaultTestProvider
     */
    public function testDefault($value, $expected): void
    {
        $filter = new DenyListFilter();
        $this->assertSame($expected, $filter->filter($value));
    }

    /**
     * @dataProvider listTestProvider
     */
    public function testList(bool $strict, array $list, array $testData): void
    {
        $filter = new DenyListFilter([
            'strict' => $strict,
            'list'   => $list,
        ]);
        foreach ($testData as $data) {
            [$value, $expected] = $data;
            $message            = sprintf(
                '%s (%s) is not filtered as %s; type = %s, strict = %b',
                var_export($value, true),
                gettype($value),
                var_export($expected, true),
                gettype($value),
                (int) $strict
            );
            $this->assertSame($expected, $filter->filter($value), $message);
        }
    }

    public static function defaultTestProvider(): array
    {
        return [
            ['test',   'test'],
            [0,        0],
            [0.1,      0.1],
            [[], []],
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
                    ['test',   null],
                    [0,        null],
                    [null,     null],
                    [false,    false],
                    [0.0,      0.0],
                    [[], []],
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
                    [[], []],
                ],
            ],
        ];
    }
}
