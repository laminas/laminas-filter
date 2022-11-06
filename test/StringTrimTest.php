<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\StringTrim;
use PHPUnit\Framework\TestCase;
use stdClass;

use function utf8_encode;

class StringTrimTest extends TestCase
{
    private StringTrim $filter;

    public function setUp(): void
    {
        $this->filter = new StringTrim();
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testBasic(): void
    {
        $filter         = $this->filter;
        $valuesExpected = [
            'string' => 'string',
            ' str '  => 'str',
            "\ns\t"  => 's',
        ];
        foreach ($valuesExpected as $input => $output) {
            self::assertSame($output, $filter($input));
        }
    }

    /**
     * Ensures that the filter follows expected behavior
     */
    public function testUtf8(): void
    {
        self::assertSame('a', $this->filter->filter(utf8_encode("\xa0a\xa0")));
    }

    /**
     * Ensures that getCharList() returns expected default value
     */
    public function testGetCharList(): void
    {
        self::assertSame(null, $this->filter->getCharList());
    }

    /**
     * Ensures that setCharList() follows expected behavior
     */
    public function testSetCharList(): void
    {
        $this->filter->setCharList('&');
        self::assertSame('&', $this->filter->getCharList());
    }

    /**
     * Ensures expected behavior under custom character list
     */
    public function testCharList(): void
    {
        $filter = $this->filter;
        $filter->setCharList('&');
        self::assertSame('a&b', $filter('&&a&b&&'));
    }

    /**
     * @group Laminas-7183
     */
    public function testLaminas7183(): void
    {
        $filter = $this->filter;
        self::assertSame('Зенд', $filter('Зенд'));
    }

    /**
     * @group Laminas-170
     */
    public function testLaminas170(): void
    {
        $filter = $this->filter;
        self::assertSame('Расчет', $filter('Расчет'));
    }

    /**
     * @group Laminas-7902
     */
    public function testLaminas7902(): void
    {
        $filter = $this->filter;
        self::assertSame('/', $filter('/'));
    }

    /**
     * @group Laminas-10891
     */
    public function testLaminas10891(): void
    {
        $filter = $this->filter;
        self::assertSame('Зенд', $filter('   Зенд   '));
        self::assertSame('Зенд', $filter('Зенд   '));
        self::assertSame('Зенд', $filter('   Зенд'));

        $trimCharlist = " \t\n\r\x0B・。";
        $filter       = new StringTrim($trimCharlist);
        self::assertSame('Зенд', $filter->filter('。  Зенд  。'));
    }

    /** @return list<array{0: mixed}> */
    public function getNonStringValues(): array
    {
        return [
            [1],
            [1.0],
            [true],
            [false],
            [null],
            [[1, 2, 3]],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider getNonStringValues
     */
    public function testShouldNotFilterNonStringValues(mixed $value): void
    {
        $filtered = $this->filter->filter($value);
        self::assertSame($value, $filtered);
    }

    /**
     * Ensures expected behavior with '0' as character list
     *
     * @group 6261
     */
    public function testEmptyCharList(): void
    {
        $filter = $this->filter;
        $filter->setCharList('0');
        self::assertSame('a0b', $filter('00a0b00'));

        $filter->setCharList('');
        self::assertSame('str', $filter(' str '));
    }
}
